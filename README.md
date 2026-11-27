# Laravel Code Auditor

**Don't just detect dead code. Discover how it became dead.**

This **Alpha release** helps Laravel developers find potentially unused methods in `app/`. When Git evidence is available, it shows the commit where a method's last detected reference disappeared.

## Features

- `/code-audit` dashboard with file, method, line, last modification author, and commit metadata.
- Read-only comparison of `HEAD` with its first parent for dead code origin evidence.
- Removed call target, previous and current reference counts, origin commit details, and a rule-based explanation.
- No database, external API, telemetry, or code modification.

## Requirements

- PHP 8.4 (`^8.4`, meaning 8.4 through 8.x).
- Laravel 13.43 or later in the 13.x series, based on the declared `illuminate/*` constraints (`^13.43`).
- Composer 2 to install the package. Validation used Composer 2.9.5.
- Git available to the PHP process for commit and diff analysis.
- A local Laravel development environment (`APP_ENV=local`). The dashboard returns 404 in other environments.

## Compatibility

The declared constraints allow PHP `>=8.4 <9.0` and Illuminate components `>=13.43 <14.0`. Only these combinations have been tested:

| PHP | Laravel | Status |
| --- | --- | --- |
| 8.4.28 | 13.43.0 | Tested |
| 8.4.28 | 13.44.1 | Tested |
| 8.4.28 | Other 13.43+ versions in 13.x | Allowed by constraints; untested |
| Other 8.4–8.x versions | 13.43+ in 13.x | Allowed by constraints; untested |
| Below 8.4 | Any | Outside declared constraints; unsupported |
| Any | Below 13.43 or 14.x+ | Outside declared constraints; unsupported |

The table describes Composer constraints and actual validation, not a guarantee for untested versions.

## Architecture decisions

- [ADR-001: Package architecture](docs/adr/ADR-001-package-architecture.md)
- [ADR-002: Dead code origin analysis](docs/adr/ADR-002-dead-code-origin-analysis.md)
- [ADR-003: Security and distribution](docs/adr/ADR-003-security-and-distribution.md)

## Installation

Run these commands from your Laravel project root:

```bash
composer config repositories.code-auditor vcs https://github.com/hangtuah/laravel-code-auditor.git
composer require --dev hangtuah/laravel-code-auditor:v0.1.0-alpha.2
```

Composer installs directly from GitHub; Packagist is not required. While the repository is private, installation requires GitHub access. Laravel discovers the service provider automatically. The `--dev` flag keeps the package in `require-dev`.

## Quick Start

From your Laravel project root, set `APP_ENV=local` in your local `.env` file, then run:

```bash
php artisan route:clear
php artisan serve --host=127.0.0.1
```

Open [http://127.0.0.1:8000/code-audit](http://127.0.0.1:8000/code-audit). The page shows findings and any available origin investigations.

## Security and privacy

The scanner reads PHP source in `app/`, `routes/`, `database/`, `resources/`, and `tests/`. It skips `config/`, `vendor/`, `node_modules/`, and paths under `generated/`. It does not read `.env` files. Git commands use argument arrays or shell-escaped paths. The dashboard escapes values as HTML and shows only the removed call target from a relevant diff line, without arguments or adjacent code. No data is sent to an external service.

This package is intended for local development only. A local environment exposed on a network could expose method names and commit metadata; bind the development server to `127.0.0.1`.

## Interpretation and limitations

Findings are **potentially unused code**, not proof that methods are safe to remove. Reference matching is name-based. Dynamic invocation, reflection, framework conventions, and same-named methods can cause false results. Origin analysis compares only `HEAD` and its first parent, and requires the method in both snapshots plus a removed reference line. Uncommitted changes are not treated as committed historical evidence. Git evidence cannot prove developer intentions. If evidence is insufficient, the dashboard reports **Origin not established**.

## Uninstallation

Run from your Laravel project root:

```bash
composer remove --dev hangtuah/laravel-code-auditor
composer config --unset repositories.code-auditor
php artisan route:clear
```

## License

[MIT License](LICENSE), Copyright (c) 2026 Mohd Fairus.
