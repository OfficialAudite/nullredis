# Contributing to NullRedis

Thanks for considering a contribution! This document explains how to propose changes and the basic coding standards for this project.

## Getting Started
- Fork the repo and create a feature branch from `main` (or `master`).
- Make your changes in small, focused commits.
- Add/adjust tests in `tests/` where applicable.
- Run tests locally: `composer install && composer test` (from the `nullredisgit/` directory).

## Coding Guidelines
- PHP 8.0+ syntax.
- PSR-4 autoloading (`NullRedis\\` namespace).
- Keep the public API minimal and focused on the existing Redis-like subset.
- Prefer clarity over cleverness; include short comments for non-obvious code.

## File-backed Cache Design
- Each key is stored as a JSON file in `cache/` sharded by the first 2 characters of `sha1(key)`.
- TTL is applied lazily on read; expired files are deleted when accessed.
- Sorted-sets and sets are implemented with simple PHP arrays; avoid heavy features.

## Tests
- Add unit tests for new features or bug fixes in `tests/`.
- Avoid external services; tests should run offline.

## Commit Messages
- Use descriptive messages (e.g., `feat: add expire() to sets`, `fix: handle concurrent zadd`).

## Pull Requests
- Describe what changed and why.
- Link related issues if any.
- Ensure CI is green.

## Release Process
- Maintain `CHANGELOG.md`.
- Use semantic versioning (e.g., `v0.1.0`).
- Tag releases and publish to Packagist if desired.

Thanks again for helping improve NullRedis!
