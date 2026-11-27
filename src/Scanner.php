<?php

namespace LaravelCodeAuditor;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Scanner
{
    public function scan(string $root): array
    {
        $root = rtrim($root, '/');
        $sources = [];
        foreach (['app', 'routes', 'database', 'resources', 'tests'] as $directory) {
            if (! is_dir($root.'/'.$directory)) {
                continue;
            }
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/'.$directory)) as $file) {
                if ($file->isFile() && $file->getExtension() === 'php' && ! str_contains($file->getPathname(), '/generated/')) {
                    $sources[substr($file->getPathname(), strlen($root) + 1)] = file_get_contents($file->getPathname());
                }
            }
        }

        $findings = [];
        foreach ($sources as $path => $source) {
            if (! str_starts_with($path, 'app/')) {
                continue;
            }
            $tokens = token_get_all($source);
            $line = 1;
            foreach ($tokens as $index => $token) {
                if (is_array($token)) {
                    $line = $token[2];
                }
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
                $nameToken = $tokens[$next] ?? null;
                if (! is_array($nameToken) || $nameToken[0] !== T_STRING) {
                    continue;
                }
                $name = $nameToken[1];
                if (str_starts_with($name, '__')) {
                    continue;
                }
                // Declaration itself is one occurrence. Other occurrences are possible calls or references.
                $references = 0;
                foreach ($sources as $content) {
                    $references += preg_match_all('/\b'.preg_quote($name, '/').'\s*\(/i', $content);
                }
                if ($references > 1) {
                    continue;
                }
                $findings[] = array_merge([
                    'file' => $path,
                    'method' => $name,
                    'line' => $line,
                    'status' => 'Potentially Unused',
                ], $this->gitInfo($root, $path, $line));
            }
        }

        return $findings;
    }

    private function gitInfo(string $root, string $path, int $line): array
    {
        $empty = ['author' => 'Unavailable', 'hash' => '—', 'date' => '—'];
        $command = 'git -C '.escapeshellarg($root).' blame --porcelain -L '.$line.','.$line.' -- '.escapeshellarg($path).' 2>/dev/null';
        $output = shell_exec($command);
        if (! $output || ! preg_match('/^([0-9a-f]{40})\s/m', $output, $hash) || preg_match('/^0{40}$/', $hash[1])) {
            return $empty;
        }
        preg_match('/^author (.+)$/m', $output, $author);
        preg_match('/^author-time (\d+)$/m', $output, $time);

        return [
            'author' => $author[1] ?? 'Unavailable',
            'hash' => substr($hash[1], 0, 12),
            'date' => isset($time[1]) ? gmdate('Y-m-d', (int) $time[1]) : '—',
        ];
    }
}
