<?php
/**
 * Cache Service
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Server-side caching implementation supporting file-based caching.
 * Future-ready for Redis/Memcached integration.
 */

declare(strict_types=1);

namespace App\Services;

class Cache
{
    private static ?self $instance = null;
    private string $driver;
    private string $path;
    private string $prefix;
    private int $defaultTtl;

    private function __construct()
    {
        $this->driver = env('CACHE_DRIVER', 'file');
        $this->path = STORAGE_PATH . '/cache';
        $this->prefix = env('CACHE_PREFIX', 'pricetag_');
        $this->defaultTtl = (int) env('CACHE_TTL', 3600);

        // Ensure cache directory exists
        if (!is_dir($this->path)) {
            mkdir($this->path, 0755, true);
        }
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get cached value
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $filename = $this->getFilename($key);

        if (!file_exists($filename)) {
            return $default;
        }

        $content = file_get_contents($filename);
        if ($content === false) {
            return $default;
        }

        $data = unserialize($content);

        // Check expiration
        if ($data['expires_at'] !== null && $data['expires_at'] < time()) {
            $this->forget($key);
            return $default;
        }

        return $data['value'];
    }

    /**
     * Store value in cache
     */
    public function put(string $key, mixed $value, ?int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->defaultTtl;
        $filename = $this->getFilename($key);

        $data = [
            'value' => $value,
            'created_at' => time(),
            'expires_at' => $ttl > 0 ? time() + $ttl : null,
        ];

        $result = file_put_contents($filename, serialize($data), LOCK_EX);

        return $result !== false;
    }

    /**
     * Store value forever (no expiration)
     */
    public function forever(string $key, mixed $value): bool
    {
        return $this->put($key, $value, 0);
    }

    /**
     * Check if key exists in cache
     */
    public function has(string $key): bool
    {
        return $this->get($key, $this) !== $this;
    }

    /**
     * Remove item from cache
     */
    public function forget(string $key): bool
    {
        $filename = $this->getFilename($key);

        if (file_exists($filename)) {
            return unlink($filename);
        }

        return true;
    }

    /**
     * Get and remove item from cache
     */
    public function pull(string $key, mixed $default = null): mixed
    {
        $value = $this->get($key, $default);
        $this->forget($key);
        return $value;
    }

    /**
     * Get or store value if not exists
     */
    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->put($key, $value, $ttl);

        return $value;
    }

    /**
     * Get or store forever if not exists
     */
    public function rememberForever(string $key, callable $callback): mixed
    {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->forever($key, $value);

        return $value;
    }

    /**
     * Increment a cached value
     */
    public function increment(string $key, int $amount = 1): int
    {
        $value = (int) $this->get($key, 0);
        $value += $amount;
        $this->forever($key, $value);
        return $value;
    }

    /**
     * Decrement a cached value
     */
    public function decrement(string $key, int $amount = 1): int
    {
        return $this->increment($key, -$amount);
    }

    /**
     * Clear all cache
     */
    public function flush(): bool
    {
        $files = glob($this->path . '/' . $this->prefix . '*');

        if ($files === false) {
            return false;
        }

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        return true;
    }

    /**
     * Clear expired cache entries
     */
    public function gc(): int
    {
        $files = glob($this->path . '/' . $this->prefix . '*');
        $cleared = 0;

        if ($files === false) {
            return 0;
        }

        foreach ($files as $file) {
            if (!is_file($file)) {
                continue;
            }

            $content = file_get_contents($file);
            if ($content === false) {
                continue;
            }

            $data = @unserialize($content);
            if ($data === false) {
                unlink($file);
                $cleared++;
                continue;
            }

            if (isset($data['expires_at']) && $data['expires_at'] !== null && $data['expires_at'] < time()) {
                unlink($file);
                $cleared++;
            }
        }

        return $cleared;
    }

    /**
     * Get cache statistics
     */
    public function stats(): array
    {
        $files = glob($this->path . '/' . $this->prefix . '*');
        $totalSize = 0;
        $count = 0;
        $expired = 0;

        if ($files !== false) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    $count++;
                    $totalSize += filesize($file);

                    $content = file_get_contents($file);
                    if ($content !== false) {
                        $data = @unserialize($content);
                        if ($data && isset($data['expires_at']) && $data['expires_at'] !== null && $data['expires_at'] < time()) {
                            $expired++;
                        }
                    }
                }
            }
        }

        return [
            'driver' => $this->driver,
            'path' => $this->path,
            'total_items' => $count,
            'expired_items' => $expired,
            'total_size' => $totalSize,
            'total_size_formatted' => $this->formatBytes($totalSize),
        ];
    }

    /**
     * Tag-based cache key
     */
    public function tags(array $tags): TaggedCache
    {
        return new TaggedCache($this, $tags);
    }

    /**
     * Get filename for cache key
     */
    private function getFilename(string $key): string
    {
        $hash = md5($key);
        return $this->path . '/' . $this->prefix . $hash . '.cache';
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}

/**
 * Tagged Cache for grouped cache invalidation
 */
class TaggedCache
{
    private Cache $cache;
    private array $tags;

    public function __construct(Cache $cache, array $tags)
    {
        $this->cache = $cache;
        $this->tags = $tags;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->cache->get($this->taggedKey($key), $default);
    }

    public function put(string $key, mixed $value, ?int $ttl = null): bool
    {
        // Store key in tag index
        foreach ($this->tags as $tag) {
            $tagKey = 'tag:' . $tag;
            $keys = $this->cache->get($tagKey, []);
            $keys[] = $this->taggedKey($key);
            $keys = array_unique($keys);
            $this->cache->forever($tagKey, $keys);
        }

        return $this->cache->put($this->taggedKey($key), $value, $ttl);
    }

    public function forget(string $key): bool
    {
        return $this->cache->forget($this->taggedKey($key));
    }

    public function flush(): bool
    {
        foreach ($this->tags as $tag) {
            $tagKey = 'tag:' . $tag;
            $keys = $this->cache->get($tagKey, []);

            foreach ($keys as $key) {
                $this->cache->forget($key);
            }

            $this->cache->forget($tagKey);
        }

        return true;
    }

    private function taggedKey(string $key): string
    {
        return implode(':', $this->tags) . ':' . $key;
    }
}
