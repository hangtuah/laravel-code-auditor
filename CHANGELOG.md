# Changelog

## v0.1.0 — compatibility release

- Replaced the PHP 8.4-only `array_any()` call and expanded declared PHP, Illuminate, and Symfony Process ranges.
- Validated PHP 8.2, 8.3, and 8.4 with secure Laravel 12 dependencies, plus PHP 8.4 with Laravel 13.
- Renamed the Composer package to `hangtuah/code-auditor` without changing the repository, namespace, or route.
- The reported Laravel 12.31.1 / Symfony Process 7.3.3 combination remains untested because Composer blocks those pinned versions on security advisory grounds.

## v0.1.0-alpha.2 — public alpha preparation

- Added the MIT License with the confirmed copyright notice.
- Updated HTTPS GitHub installation instructions and public distribution documentation.
- No scanner, origin analysis, or dashboard behavior changes.

## v0.1.0-alpha.1 — private internal release

- Initial internal alpha of the potentially unused method dashboard.
- Git blame details and read-only dead code origin analysis.
- Local-environment access restriction and limited diff evidence display.
