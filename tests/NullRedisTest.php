<?php

use NullRedis\NullRedis;
use PHPUnit\Framework\TestCase;

final class NullRedisTest extends TestCase
{
    private string $tmp;

    protected function setUp(): void
    {
        $this->tmp = sys_get_temp_dir() . '/nullredis-test-' . uniqid();
        @mkdir($this->tmp, 0777, true);
    }

    protected function tearDown(): void
    {
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->tmp, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($it as $file) {
            if ($file->isDir()) @rmdir($file->getRealPath()); else @unlink($file->getRealPath());
        }
        @rmdir($this->tmp);
    }

    public function testSetGet(): void
    {
        $cache = new NullRedis($this->tmp);
        $cache->set('foo', 'bar');
        $this->assertSame('bar', $cache->get('foo'));
    }

    public function testSetExExpires(): void
    {
        $cache = new NullRedis($this->tmp);
        $cache->setex('ttl', 1, 'x');
        sleep(2);
        $this->assertNull($cache->get('ttl'));
    }

    public function testSetMembers(): void
    {
        $cache = new NullRedis($this->tmp);
        $cache->sAdd('set', 'a');
        $cache->sAdd('set', 'b');
        $members = $cache->sMembers('set');
        sort($members);
        $this->assertSame(['a','b'], $members);
    }

    public function testZAddZCard(): void
    {
        $cache = new NullRedis($this->tmp);
        $key = 'z';
        $cache->zadd($key, 1, 'a');
        $cache->zadd($key, 2, 'b');
        $this->assertSame(2, $cache->zcard($key));
        $cache->zremrangebyscore($key, 0, 1);
        $this->assertSame(1, $cache->zcard($key));
    }
}


