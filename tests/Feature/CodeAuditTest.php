<?php

use LaravelCodeAuditor\Scanner;

it('shows local audit findings and git metadata', function () {
    config(['app.env' => 'local']);
    $response = $this->get('/code-audit');
    $response->assertOk()->assertSee('Laravel Code Auditor')->assertSee('Potentially Unused');

    $findings = app(Scanner::class)->scan(base_path());
    expect($findings)->not->toBeEmpty();
    expect($findings[0])->toHaveKeys(['file', 'method', 'line', 'author', 'hash', 'date']);
});

it('hides the audit outside local development', function () {
    config(['app.env' => 'production']);
    $this->get('/code-audit')->assertNotFound();
});
