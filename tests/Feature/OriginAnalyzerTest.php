<?php

use LaravelCodeAuditor\OriginAnalyzer;
use Symfony\Component\Process\Process;

function fixtureGit(string $root, array $arguments): void
{
    $process = new Process(array_merge(['git', '-C', $root], $arguments));
    $process->mustRun();
}

it('uses committed reference removal as origin evidence and ignores working changes', function () {
    $root = sys_get_temp_dir().'/audit-origin-'.bin2hex(random_bytes(6));
    mkdir($root.'/app', 0777, true);
    fixtureGit($root, ['init', '-q']);
    fixtureGit($root, ['config', 'user.name', 'Fixture Author']);
    fixtureGit($root, ['config', 'user.email', 'fixture@example.test']);
    $source = $root.'/app/Example.php';
    file_put_contents($source, "<?php\nclass Example {\n public function lost() {}\n public function used() {}\n public function old() {}\n public function run() {\n  \$this->lost();\n  \$this->used();\n }\n}");
    fixtureGit($root, ['add', '.']);
    fixtureGit($root, ['commit', '-qm', 'Add methods and references']);

    $finding = fn ($name) => ['file' => 'app/Example.php', 'method' => $name, 'line' => 1, 'author' => 'Method Author'];
    $analyzer = new OriginAnalyzer;
    expect($analyzer->analyze($root, [$finding('lost')]))->toBe([]); // No parent yet.

    file_put_contents($source, "<?php\nclass Example {\n public function lost() {}\n public function used() {}\n public function old() {}\n public function run() {\n  \$this->used();\n }\n}");
    fixtureGit($root, ['add', '.']);
    fixtureGit($root, ['commit', '-qm', 'Remove lost call']);
    $results = $analyzer->analyze($root, [$finding('lost'), $finding('used'), $finding('old')]);
    expect($results)->toHaveCount(1)
        ->and($results[0]['method'])->toBe('lost')
        ->and($results[0]['previous_references'])->toBe(1)
        ->and($results[0]['current_references'])->toBe(0)
        ->and($results[0]['origin_author'])->toBe('Fixture Author')
        ->and($results[0]['origin_message'])->toBe('Remove lost call')
        ->and($results[0]['diff'])->toContain('$this->lost(');

    // A local edit cannot change what HEAD proves.
    file_put_contents($source, "<?php\nclass Example {\n public function lost() {}\n public function used() {}\n public function old() {}\n public function run() {\n  \$this->lost();\n  \$this->used();\n }\n}");
    expect($analyzer->analyze($root, [$finding('lost')]))->toHaveCount(1);
});
