# ADR-003: Security and distribution

**Status: Accepted**

## Context

The dashboard displays source-derived and Git-derived information, so access and output need limits before wider distribution.

## Decision

Return 404 unless `APP_ENV=local`. Skip `config/`, `.env`, dependency directories, and generated paths. Escape dashboard output and display only removed call targets, without arguments or adjacent diff code. Use Git locally; send no data to external services. Distribute through a public GitHub Composer VCS repository after the owner changes visibility, without Packagist.

## Rationale

These controls keep the release focused on local analysis and reduce accidental disclosure of configuration or source content.

## Consequences

Consumers can install through HTTPS after public visibility is enabled and should bind the development server to `127.0.0.1`. The package uses the MIT License.

## Known Limitations

`APP_ENV=local` is a configuration gate, not authentication. Method names, file paths, commit messages, and author names remain visible to anyone who can reach the local dashboard.
