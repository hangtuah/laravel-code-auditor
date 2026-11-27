<?php

namespace LaravelCodeAuditor;

use Symfony\Component\Process\Process;

class OriginAnalyzer
{
    private const DIRECTORIES = ['app/', 'routes/', 'database/', 'resources/', 'tests/'];

    public function analyze(string $root, array $findings): array
    {
        $parent = $this->git($root, ['rev-parse', '--verify', 'HEAD^']);
        $head = $this->git($root, ['rev-parse', '--verify', 'HEAD']);
        if ($parent === null || $head === null) {
            return [];
        }

        $before = $this->snapshot($root, trim($parent));
        $after = $this->snapshot($root, trim($head));
        if ($before === null || $after === null) {
            return [];
        }

        $metadata = $this->git($root, ['show', '-s', '--format=%H%n%an%n%aI%n%s', 'HEAD']);
        $parts = explode("\n", trim($metadata ?? ''));
        $results = [];
        foreach ($findings as $finding) {
            $path = $finding['file'];
            $name = $finding['method'];
            if (! isset($before[$path], $after[$path]) ||
                ! $this->declares($before[$path], $name) || ! $this->declares($after[$path], $name)) {
                continue;
            }
            $previous = $this->countReferences($before, $name);
            $current = $this->countReferences($after, $name);
            if ($previous < 1 || $current !== 0) {
                continue;
            }
            $removed = $this->removedReferences($root, $name, $before, $after);
            if ($removed === []) {
                continue;
            }
            $results[] = array_merge($finding, [
                'origin_author' => $parts[1] ?? 'Origin not established',
                'origin_commit' => isset($parts[0]) ? substr($parts[0], 0, 12) : 'Origin not established',
                'origin_date' => $parts[2] ?? '—',
                'origin_message' => $parts[3] ?? '—',
                'previous_references' => $previous,
                'current_references' => $current,
                'diff' => implode("\n", $removed),
                'cause' => 'Evidence: HEAD removes the shown reference lines. Inference: this may have left the method unused.',
            ]);
        }

        return $results;
    }

    private function snapshot(string $root, string $revision): ?array
    {
        $listing = $this->git($root, ['ls-tree', '-r', '--name-only', '-z', $revision]);
        if ($listing === null) {
            return null;
        }
        $sources = [];
        foreach (explode("\0", $listing) as $path) {
            if (! str_ends_with($path, '.php') ||
                ! array_any(self::DIRECTORIES, fn ($prefix) => str_starts_with($path, $prefix)) ||
                str_contains($path, '/generated/')) {
                continue;
            }
            $content = $this->git($root, ['show', $revision.':'.$path]);
            if ($content !== null) {
                $sources[$path] = $content;
            }
        }

        return $sources;
    }

    private function declares(string $source, string $name): bool
    {
        $tokens = token_get_all($source);
        foreach ($tokens as $index => $token) {
            if (! is_array($token) || $token[0] !== T_FUNCTION) {
                continue;
            }
            $next = $index + 1;
            while (isset($tokens[$next]) && is_array($tokens[$next]) && $tokens[$next][0] === T_WHITESPACE) {
                $next++;
            }
            if (($tokens[$next] ?? null) === '&') {
                $next++;
            }
            if (is_array($tokens[$next] ?? null) && strcasecmp($tokens[$next][1], $name) === 0) {
                return true;
            }
        }

        return false;
    }

    private function countReferences(array $sources, string $name): int
    {
        $count = 0;
        foreach ($sources as $source) {
            $tokens = token_get_all($source);
            foreach ($tokens as $index => $token) {
                if (! is_array($token) || $token[0] !== T_STRING || strcasecmp($token[1], $name) !== 0) {
                    continue;
                }
                $previous = $index - 1;
                while ($previous >= 0 && is_array($tokens[$previous]) && $tokens[$previous][0] === T_WHITESPACE) {
                    $previous--;
                }
                if ($previous >= 0 && is_array($tokens[$previous]) && $tokens[$previous][0] === T_FUNCTION) {
                    continue;
                }
                $next = $index + 1;
                while (isset($tokens[$next]) && is_array($tokens[$next]) && $tokens[$next][0] === T_WHITESPACE) {
                    $next++;
                }
                if (($tokens[$next] ?? null) === '(') {
                    $count++;
                }
            }
        }

        return $count;
    }

    private function removedReferences(string $root, string $name, array $before, array $after): array
    {
        $paths = array_unique(array_merge(array_keys($before), array_keys($after)));
        $evidence = [];
        foreach ($paths as $path) {
            if (($before[$path] ?? null) === ($after[$path] ?? null)) {
                continue;
            }
            $diff = $this->git($root, ['diff', '--unified=0', 'HEAD^', 'HEAD', '--', $path]);
            if ($diff === null) {
                continue;
            }
            foreach (explode("\n", $diff) as $line) {
                if (str_starts_with($line, '-') && ! str_starts_with($line, '---') &&
                    $this->countReferences(['diff' => '<?php '.substr($line, 1)], $name) > 0) {
                    // Only show the call target. Arguments or adjacent code may contain secrets.
                    preg_match('/(?:\$[A-Za-z_][A-Za-z0-9_]*\s*(?:->|\?->)|[A-Za-z_][A-Za-z0-9_]*\s*::)?\s*'.preg_quote($name, '/').'\s*\(/i', $line, $match);
                    $evidence[] = $path.': - '.trim($match[0] ?? $name.'(');
                }
            }
        }

        return array_slice($evidence, 0, 10);
    }

    private function git(string $root, array $arguments): ?string
    {
        $process = new Process(array_merge(['git', '-C', $root], $arguments));
        $process->run();

        return $process->isSuccessful() ? $process->getOutput() : null;
    }
}
