# Contributing (Internal)

## Branching & Commits
- Main is protected; use short-lived feature branches.
- Conventional commits (feat, fix, docs, chore, refactor, test).

## Code Style & Quality
- PHP: PSR-12, strict types; JS: modern ES with WP build tools.
- KISS/DRY; meaningful names; avoid deep nesting; early returns.

## Security & Validation
- Register meta with sanitize/type/default; escape on output.
- Nonces and capability checks on mutations; REST arg schemas.

## Reviews & Testing
- PR requires at least one review.
- Unit/integration tests where applicable; run smoke checks before merge.

## Scope
- Gutenberg-only; block themes; no Classic editor/theme support.
