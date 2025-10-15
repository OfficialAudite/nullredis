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

    public function testConnectAuthPing(): void
    {
        $cache = new NullRedis($this->tmp);
        $this->assertTrue($cache->connect());
        $this->assertTrue($cache->auth('any'));
        $this->assertTrue($cache->ping());
    }

    public function testExists(): void
    {
        $cache = new NullRedis($this->tmp);
        $this->assertFalse($cache->exists('missing'));
        $cache->set('exists-key', '1');
        $this->assertTrue($cache->exists('exists-key'));
        $cache->setex('ttl-key', 1, 'x');
        $this->assertTrue($cache->exists('ttl-key'));
        sleep(2);
        $this->assertFalse($cache->exists('ttl-key'));
    }

    public function testExpire(): void
    {
        $cache = new NullRedis($this->tmp);
        $cache->set('exp', 'v');
        $this->assertTrue($cache->expire('exp', 1));
        sleep(2);
        $this->assertNull($cache->get('exp'));
    }

    public function testFlushAll(): void
    {
        $cache = new NullRedis($this->tmp);
        $cache->set('a', '1');
        $cache->sAdd('s', 'm');
        $cache->zadd('z', 1, 'm');
        $this->assertTrue($cache->exists('a'));
        $this->assertSame(['m'], $cache->sMembers('s'));
        $this->assertSame(1, $cache->zcard('z'));
        $cache->flushAll();
        $this->assertFalse($cache->exists('a'));
        $this->assertSame([], $cache->sMembers('s'));
        $this->assertSame(0, $cache->zcard('z'));
    }

    public function testZRemRangeByScoreReturnsCount(): void
    {
        $cache = new NullRedis($this->tmp);
        $key = 'zr';
        $cache->zadd($key, 1, 'a');
        $cache->zadd($key, 2, 'b');
        $cache->zadd($key, 3, 'c');
        $removed = $cache->zremrangebyscore($key, 1, 2);
        $this->assertSame(2, $removed);
        $this->assertSame(1, $cache->zcard($key));
    }

    public function testSMembersMissing(): void
    {
        $cache = new NullRedis($this->tmp);
        $this->assertSame([], $cache->sMembers('nope'));
    }
}


