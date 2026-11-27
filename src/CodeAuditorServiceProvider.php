<?php

namespace LaravelCodeAuditor;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CodeAuditorServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'code-auditor');

        Route::get('/code-audit', function (Scanner $scanner, OriginAnalyzer $analyzer) {
            abort_unless(config('app.env') === 'local', 404);

            $findings = $scanner->scan(base_path());

            return view('code-auditor::index', [
                'findings' => $findings,
                'investigations' => $analyzer->analyze(base_path(), $findings),
            ]);
        })->name('code-audit.index');
    }
}
