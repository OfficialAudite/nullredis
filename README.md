# NullRedis (file-backed Redis-like shim)

NullRedis is a tiny, dependency-free, file-backed shim that mimics a small subset of Redis commands:

- KV: get, set, setex, exists, expire
- Sets: sAdd, sMembers
- Sorted sets: zadd, zcard, zremrangebyscore
- Admin: flushAll

It’s useful as a drop-in fallback when Redis is unavailable, or as a lightweight cache/rate-limiter without running a server.

## Install
### Via Composer (recommended)
```bash
composer require officialaudite/nullredis
```
```php
require __DIR__ . '/vendor/autoload.php';
use NullRedis\NullRedis;
$cache = new NullRedis(__DIR__.'/cache'); // optional custom cache dir
```

### Manual (copy file)
Copy `src/NullRedis.php` into your project and include it.
```php
require_once __DIR__.'/src/NullRedis.php';
$cache = new \NullRedis\NullRedis(__DIR__.'/cache');
```

## Usage
### Basic KV
```php
use NullRedis\NullRedis;
$cache = new NullRedis(__DIR__.'/cache');
$cache->set('greeting', 'hello');
echo $cache->get('greeting'); // hello
$cache->setex('temp', 2, 'short');
sleep(3);
var_dump($cache->get('temp')); // NULL after expiry
```

### Sets
```php
$cache->sAdd('bad_agents', 'curl/7.88');
$cache->sAdd('bad_agents', 'bot');
print_r($cache->sMembers('bad_agents'));
```

### Sorted sets (rate limiting)
```php
$user = '127.0.0.1';
$key = 'rate:'.$user;
$now = time(); $window = 2; $max = 5;
$cache->zremrangebyscore($key, 0, $now - $window);
$cache->zadd($key, $now, (string)$now);
if ($cache->zcard($key) > $max) { http_response_code(429); exit('Too Many Requests'); }
$cache->expire($key, $window+1);
```

## Development
### Run tests
```bash
composer install
composer test
```

## Roadmap
- [x] KV basics: `get`, `set`, `setex`, `exists`, `expire`
- [x] Admin: `flushAll`
- [x] Sorted sets (MVP): `zadd`, `zcard`, `zremrangebyscore`
- [x] Sets (MVP): `sAdd`/`sMembers`
- [ ] KV more: `del`, `mset`, `mget`, `incr`, `decr`, `incrBy`, `decrBy`, `pexpire`, `ttl`, `pttl`, `persist`, `type`, `keys`
- [ ] Sets more: `sismember`, `srem`, `scard`
- [ ] Sorted sets more: `zrange`, `zrevrange`, `zrangebyscore`, `zrem`, `zscore`, `zcount`
- [ ] Lists: `lpush`, `rpush`, `lpop`, `rpop`, `llen`, `lrange`
- [ ] Hashes: `hset`, `hget`, `hmset`, `hmget`, `hgetall`, `hdel`, `hexists`, `hincrby`
- [ ] DB/admin: `select` (db directories), `dbsize`, `flushdb`
- [ ] Docs: compatibility matrix vs phpredis
- [ ] Optional: PHPCS and coding-style CI

Non-goals (for now): Pub/Sub, Streams, Lua (`eval`), blocking ops (`blpop`, `brpop`), Cluster.

## Notes
- Per-key JSON files under `cache/`, sharded by the first 2 chars of SHA1(key)
- Lazy TTL expiration on read
- Single-host, low-contention environments recommended

## License
MIT — see [LICENSE](./LICENSE)
