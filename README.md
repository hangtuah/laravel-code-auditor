# Laravel Code Auditor

**Don't just detect dead code. Discover how it became dead.**

Laravel Code Auditor helps developers find potentially unused methods in `app/`. When Git evidence is available, it shows the commit where a method's last detected reference disappeared.

## Features

- `/code-audit` dashboard with file, method, line, last modification author, and commit metadata.
- Read-only comparison of `HEAD` with its first parent for dead code origin evidence.
- Removed call target, previous and current reference counts, origin commit details, and a rule-based explanation.
- No database, external API, telemetry, or code modification.

## Requirements

- PHP 8.2 or later within the declared `^8.2` range.
- Laravel 12.x or 13.x, based on the declared `illuminate/*` constraints (`^12.0 || ^13.0`).
- Composer 2 to install the package. Validation used Composer 2.9.5.
- Git available to the PHP process for commit and diff analysis.
- A local Laravel development environment (`APP_ENV=local`). The dashboard returns 404 in other environments.

## Compatibility

The package declares PHP `>=8.2 <9.0`, Illuminate components `>=12.0 <14.0`, and Symfony Process `^7.2 || ^8.0`. The following combinations were tested in isolated Laravel applications:

| PHP | Laravel | Symfony Process | Result |
| --- | --- | --- | --- |
| 8.2.36 | 12.74.0 | 7.4.24 | Tested |
| 8.3.36 | 12.74.0 | 7.4.24 | Tested |
| 8.4.28 | 12.74.0 | 7.4.24 | Tested |
| 8.4.28 | 13.44.1 | 8.1.12 | Tested |
| 8.3.36 | 12.31.1 | 7.3.3 | Installation blocked by Composer security advisories; untested |
| Other versions within declared constraints | — | — | Untested |

The reported Laravel 12.31.1 / Symfony Process 7.3.3 combination could not be tested because Composer blocks those pinned versions for security advisories. This does not establish a package incompatibility. Declared constraints do not guarantee that every version can be installed safely.

Composer reported Laravel 12.31.1 advisories `PKSA-yxbx-3c9s-cjt9`, `PKSA-b41t-wbzk-9q51`, `PKSA-d5tc-s1qs-h781`, `PKSA-m5cs-t1y6-qpcs`, `PKSA-3r5d-mb8f-1qw9`, and `PKSA-mdq4-51ck-6kdq`. It reported Symfony Process 7.3.3 advisory `PKSA-rkkf-636k-qjb3`.

## Architecture decisions

- [ADR-001: Package architecture](docs/adr/ADR-001-package-architecture.md)
- [ADR-002: Dead code origin analysis](docs/adr/ADR-002-dead-code-origin-analysis.md)
- [ADR-003: Security and distribution](docs/adr/ADR-003-security-and-distribution.md)

## Installation

For `v0.1.0`, run these commands from your Laravel project root:

```bash
composer config repositories.audit vcs https://github.com/hangtuah/laravel-code-auditor.git
composer require --dev hangtuah/code-auditor
php artisan route:clear
```

Composer installs directly from GitHub; Packagist is not required. While the repository is private, installation requires GitHub access. Laravel discovers the service provider automatically. The `--dev` flag keeps the package in `require-dev`.

### Migrating from the previous package name

Run from your Laravel project root if you installed `hangtuah/laravel-code-auditor`:

```bash
composer remove --dev hangtuah/laravel-code-auditor
composer config --unset repositories.code-auditor
composer config repositories.audit vcs https://github.com/hangtuah/laravel-code-auditor.git
composer require --dev hangtuah/code-auditor
php artisan route:clear
```

The repository URL, PHP namespace, and `/code-audit` route are unchanged.

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
composer remove --dev hangtuah/code-auditor
composer config --unset repositories.audit
php artisan route:clear
```

## License

[MIT License](LICENSE), Copyright (c) 2026 Mohd Fairus.
