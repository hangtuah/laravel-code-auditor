# ADR-001: Package architecture

**Status: Accepted**

## Context

The auditor began inside a Laravel application and needs reuse without shipping that host application.

## Decision

Ship a standalone Composer library with PSR-4 source, a Laravel auto-discovered service provider, a bundled Blade view, and the `/code-audit` route.

## Rationale

Composer path and private VCS installation can use the same package. Auto-discovery avoids host provider edits.

## Consequences

The package can be installed in `require-dev`; the dashboard runs inside the host Laravel application.

## Known Limitations

The dashboard performs scanning during the request and is intended for local development only.
