<?php
require __DIR__ . '/../src/NullRedis.php';
use NullRedis\NullRedis;

$cache = new NullRedis(__DIR__ . '/../cache');

$cache->set('greeting', 'hello');
echo $cache->get('greeting'), PHP_EOL; // hello

$cache->setex('temp', 2, 'short');
sleep(3);
var_dump($cache->get('temp')); // NULL after expiry

