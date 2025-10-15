# Changelog

All notable changes to this project will be documented in this file.

## [0.1.1] - 2025-10-16
- docs: add Composer autoload instructions to README
- ci: fix workflow to run from repo root
- chore: add CONTRIBUTING and CHANGELOG
- changed composer from nullredis/nullredis to officialaudite/nullredis
- updated readme.md to include composer install

## [0.1.0] - 2025-10-15
- First public release
- KV: get, set, setex, exists, expire
- Sets: sAdd, sMembers
- Sorted sets: zadd, zcard, zremrangebyscore
- Admin: flushAll
- Per-key JSON files with sharding; lazy TTL expiration
