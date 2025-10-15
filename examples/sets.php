<?php
require __DIR__ . '/../src/NullRedis.php';
use NullRedis\NullRedis;

$cache = new NullRedis(__DIR__ . '/../cache');

$cache->sAdd('bad_agents', 'curl/7.88');
$cache->sAdd('bad_agents', 'bot');

print_r($cache->sMembers('bad_agents'));

