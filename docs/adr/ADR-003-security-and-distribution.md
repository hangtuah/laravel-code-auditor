# ADR-003: Security and distribution

**Status: Accepted**

## Context

The dashboard displays source-derived and Git-derived information, so access and output need limits before internal distribution.

## Decision

Return 404 unless `APP_ENV=local`. Skip `config/`, `.env`, dependency directories, and generated paths. Escape dashboard output and display only removed call targets, without arguments or adjacent diff code. Use Git locally; send no data to external services. Distribute through a private GitHub Composer VCS repository, without Packagist.

## Rationale

These controls keep the release focused on local analysis and reduce accidental disclosure of configuration or source content.

## Consequences

Teams need private repository access and should bind the development server to `127.0.0.1`. No license has been selected.

## Known Limitations

`APP_ENV=local` is a configuration gate, not authentication. Method names, file paths, commit messages, and author names remain visible to anyone who can reach the local dashboard.
