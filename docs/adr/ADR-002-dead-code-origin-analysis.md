# ADR-002: Dead code origin analysis

**Status: Accepted**

## Context

An unused method finding alone does not explain when its references disappeared.

## Decision

Reuse the scanner's potentially unused findings. Match references by method name, then compare committed PHP snapshots at `HEAD` and its first parent. Investigate a method only when it exists in both snapshots, references change from at least one to zero, and a removed call appears in the diff. Show commit metadata and a rule-based explanation that separates evidence from inference.

## Rationale

A two-commit, read-only comparison is small and produces inspectable evidence without changing the working tree.

## Consequences

Findings with insufficient evidence report **Origin not established**. Uncommitted edits do not become historical evidence.

## Known Limitations

Name matching can miss dynamic calls or confuse same-named methods. Earlier history is not searched. Git cannot prove developer intent.
