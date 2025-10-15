<?php

namespace NullRedis;

class NullRedis
{
    private string $dir;

    public function __construct(string $baseDir = null)
    {
        $root = $baseDir ?: (__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'cache');
        $this->dir = realpath($root) ?: $root;
        if (!is_dir($this->dir)) @mkdir($this->dir, 0775, true);
    }

    private function shardDir(string $hash): string
    {
        $sub = substr($hash, 0, 2);
        $path = $this->dir . DIRECTORY_SEPARATOR . $sub;
        if (!is_dir($path)) @mkdir($path, 0775, true);
        return $path;
    }

    private function filePath(string $key): string
    {
        $hash = sha1($key);
        return $this->shardDir($hash) . DIRECTORY_SEPARATOR . $hash . '.json';
    }

    private function readFile(string $path)
    {
        if (!is_file($path)) return null;
        $raw = @file_get_contents($path);
        if ($raw === false) return null;
        $data = @json_decode($raw, true);
        return is_array($data) ? $data : null;
    }

    private function writeFile(string $path, array $data): void
    {
        @file_put_contents($path, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
    }

    private function getEntry(string $key)
    {
        $path = $this->filePath($key);
        $entry = $this->readFile($path);
        if ($entry === null) return null;
        $exp = (int)($entry['e'] ?? 0);
        if ($exp > 0 && $exp <= time()) { @unlink($path); return null; }
        return $entry;
    }

    // Redis-like API subset
    public function connect($host = null, $port = null) { return false; }
    public function auth($password) { return false; }
    public function ping() { return false; }

    public function get($key)
    {
        $e = $this->getEntry($key);
        return $e === null ? null : ($e['v'] ?? null);
    }

    public function setex($key, $ttl, $value)
    {
        $path = $this->filePath($key);
        $this->writeFile($path, ['v' => $value, 'e' => time() + (int)$ttl, 't' => 'v']);
        return true;
    }

    public function set($key, $value)
    {
        $path = $this->filePath($key);
        $this->writeFile($path, ['v' => $value, 'e' => 0, 't' => 'v']);
        return true;
    }

    public function exists($key)
    {
        return $this->getEntry($key) !== null;
    }

    public function expire($key, $ttl)
    {
        $path = $this->filePath($key);
        $entry = $this->readFile($path);
        if ($entry === null) return false;
        $entry['e'] = time() + (int)$ttl;
        $this->writeFile($path, $entry);
        return true;
    }

    // Sets
    public function sAdd($key, $member)
    {
        $e = $this->getEntry($key);
        $set = ($e['v'] ?? []);
        if (!is_array($set)) $set = [];
        $set[$member] = true;
        $path = $this->filePath($key);
        $this->writeFile($path, ['v' => $set, 'e' => 0, 't' => 's']);
        return true;
    }

    public function sMembers($key)
    {
        $e = $this->getEntry($key);
        $set = ($e['v'] ?? []);
        return is_array($set) ? array_keys($set) : [];
    }

    // Sorted sets (used for simple rate limiting)
    public function zremrangebyscore($key, $min, $max)
    {
        $e = $this->getEntry($key);
        $z = ($e['v'] ?? []);
        if (!is_array($z)) $z = [];
        $removed = 0;
        foreach ($z as $member => $score) {
            if ($score >= $min && $score <= $max) { unset($z[$member]); $removed++; }
        }
        $path = $this->filePath($key);
        $this->writeFile($path, ['v' => $z, 'e' => 0, 't' => 'z']);
        return $removed;
    }

    public function zadd($key, $score, $member)
    {
        $e = $this->getEntry($key);
        $z = ($e['v'] ?? []);
        if (!is_array($z)) $z = [];
        $z[$member] = $score;
        $path = $this->filePath($key);
        $this->writeFile($path, ['v' => $z, 'e' => 0, 't' => 'z']);
        return 1;
    }

    public function zcard($key)
    {
        $e = $this->getEntry($key);
        $z = ($e['v'] ?? []);
        return is_array($z) ? count($z) : 0;
    }

    public function flushAll()
    {
        if (!is_dir($this->dir)) return;
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->dir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($it as $file) {
            if ($file->isDir()) @rmdir($file->getRealPath()); else @unlink($file->getRealPath());
        }
        @mkdir($this->dir, 0775, true);
    }
}


