# Laravel Code Auditor

**Don't just detect dead code. Discover how it became dead.**

An internal Laravel development package that lists potentially unused methods in `app/` and, when evidence exists, shows the commit where their last detected reference disappeared.

## Features

- `/code-audit` dashboard with file, method, line, last modification author, and commit metadata.
- Read-only comparison of `HEAD` with its first parent for dead code origin evidence.
- Removed call target, previous and current reference counts, origin commit details, and a rule-based explanation.
- No database, external API, telemetry, or code modification.

## Requirements

- PHP 8.4 or newer within the declared `^8.4` constraint.
- Laravel 13.43 or newer within the declared `^13.43` component constraints.
- Git executable available to the PHP process for history analysis.

Verified with PHP 8.4.28 and Laravel 13.43.0 and 13.44.1. Other versions within the constraints have not been tested.

## Architecture decisions

- [ADR-001: Package architecture](docs/adr/ADR-001-package-architecture.md)
- [ADR-002: Dead code origin analysis](docs/adr/ADR-002-dead-code-origin-analysis.md)
- [ADR-003: Security and distribution](docs/adr/ADR-003-security-and-distribution.md)

## Install from a private GitHub repository

Configure access to the private repository through your normal Composer/Git credentials. In the Laravel application's `composer.json`, add:

```json
"repositories": [
  {"type": "vcs", "url": "git@github.com:hangtuah/laravel-code-auditor.git"}
]
```

Then run:

```bash
composer require --dev hangtuah/laravel-code-auditor:v0.1.0-alpha.1
php artisan package:discover
php artisan route:clear
```

Laravel discovers the service provider automatically. Keep the package in `require-dev` so production installs with `--no-dev` omit it.

## Local use

Set `APP_ENV=local`, then start the application bound to the loopback interface:

```bash
php artisan serve --host=127.0.0.1
```

Open `http://127.0.0.1:8000/code-audit`. The route returns 404 when `APP_ENV` is not `local`.

## Security and privacy

The scanner reads PHP source in `app/`, `routes/`, `database/`, `resources/`, and `tests/`. It skips `config/`, `vendor/`, `node_modules/`, and paths under `generated/`. It does not read `.env` files. Git commands use argument arrays or shell-escaped paths. The dashboard escapes values as HTML and shows only the removed call target from a relevant diff line, without arguments or adjacent code. No data is sent to an external service.

This package is intended for local development only. A local environment exposed on a network could expose method names and commit metadata; bind the development server to `127.0.0.1`.

## Interpretation and limitations

Findings are **potentially unused code**, not proof that methods are safe to remove. Reference matching is name-based. Dynamic invocation, reflection, framework conventions, and same-named methods can cause false results. Origin analysis compares only `HEAD` and its first parent, and requires the method in both snapshots plus a removed reference line. Uncommitted changes are not treated as committed historical evidence. Git evidence cannot prove developer intentions. If evidence is insufficient, the dashboard reports **Origin not established**.

## Uninstall

```bash
composer remove --dev hangtuah/laravel-code-auditor
php artisan route:clear
```

Remove the VCS repository entry from the application's `composer.json` if no longer needed.

## Release

Release target: `v0.1.0-alpha.1`. This repository has no license selected and is intended for private internal distribution. Do not publish it to Packagist.

Release validation used a separate Laravel installation and the synthetic Git check in `tests/StandaloneOriginCheck.php`. The original Laravel application's suite had 27 passing tests and one intentionally skipped `HealthCheckTest` lab exercise; that skipped host test does not exercise this package.
