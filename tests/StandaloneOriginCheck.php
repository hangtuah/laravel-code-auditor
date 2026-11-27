<?php

// Run: php8.4 tests/StandaloneOriginCheck.php /path/to/laravel-app/vendor/autoload.php
require $argv[1] ?? dirname(__DIR__).'/vendor/autoload.php';

use LaravelCodeAuditor\OriginAnalyzer;
use Symfony\Component\Process\Process;

function gitCommand(string $root, array $args): void
{
    (new Process(array_merge(['git', '-C', $root], $args)))->mustRun();
}

function check(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException($message);
    }
}

$root = sys_get_temp_dir().'/code-auditor-fixture-'.bin2hex(random_bytes(5));
mkdir($root.'/app', 0777, true);
gitCommand($root, ['init', '-q']);
gitCommand($root, ['config', 'user.name', 'Origin Fixture']);
gitCommand($root, ['config', 'user.email', 'origin@example.test']);
$file = $root.'/app/Example.php';
$source = fn (bool $lost) => "<?php\nclass Example {\n public function lost() {}\n public function used() {}\n public function old() {}\n public function run() {\n".($lost ? "  \$this->lost('SECRET_ARG');\n" : '')."  \$this->used();\n }\n}\n";
file_put_contents($file, $source(true));
gitCommand($root, ['add', '.']);
gitCommand($root, ['commit', '-qm', 'Initial references']);
$finding = fn (string $name) => ['file' => 'app/Example.php', 'method' => $name, 'line' => 3, 'author' => 'Method Author'];
$analyzer = new OriginAnalyzer;
check($analyzer->analyze($root, [$finding('lost')]) === [], 'Missing parent should have no origin');

file_put_contents($file, $source(false));
gitCommand($root, ['add', '.']);
gitCommand($root, ['commit', '-qm', 'Remove lost call']);
$results = $analyzer->analyze($root, [$finding('lost'), $finding('used'), $finding('old')]);
check(count($results) === 1, 'Only removed last reference should have an origin');
check($results[0]['previous_references'] === 1 && $results[0]['current_references'] === 0, 'Reference counts are wrong');
check($results[0]['origin_author'] === 'Origin Fixture' && $results[0]['origin_message'] === 'Remove lost call', 'Origin metadata is wrong');
check(str_contains($results[0]['diff'], '$this->lost('), 'Removed call evidence missing');
check(! str_contains($results[0]['diff'], 'SECRET_ARG'), 'Diff evidence leaked call arguments');

file_put_contents($file, $source(true));
check(count($analyzer->analyze($root, [$finding('lost')])) === 1, 'Uncommitted changes altered historical evidence');
echo "Standalone origin checks passed (6 assertions).\n";
