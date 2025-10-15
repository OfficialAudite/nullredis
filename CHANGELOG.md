# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]
- docs: add Composer autoload instructions to README
- ci: fix workflow to run from repo root
- chore: add CONTRIBUTING and CHANGELOG

## [0.1.0] - 2025-10-15
- First public release
- KV: get, set, setex, exists, expire
- Sets: sAdd, sMembers
- Sorted sets: zadd, zcard, zremrangebyscore
- Admin: flushAll
- Per-key JSON files with sharding; lazy TTL expiration
