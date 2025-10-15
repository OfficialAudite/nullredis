<?php
require __DIR__ . '/../src/NullRedis.php';
use NullRedis\NullRedis;

$cache = new NullRedis(__DIR__ . '/../cache');

$user = $_SERVER['REMOTE_ADDR'] ?? 'local';
$key = 'rate:' . $user;
$now = time();
$window = 2; $max = 5;

$cache->zremrangebyscore($key, 0, $now - $window);
$cache->zadd($key, $now, (string)$now);
$cache->expire($key, $window + 1);

if ($cache->zcard($key) > $max) {
    http_response_code(429);
    exit('Too Many Requests');
}

echo 'OK';

