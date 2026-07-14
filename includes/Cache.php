<?php
// Lightweight file-based page cache for anonymous users

class PageCache {
    private static string $dir = '';
    private static int $ttl = 300; // 5 minutes default

    public static function init(): void {
        self::$dir = __DIR__ . '/../cache/pages';
        if (!is_dir(self::$dir)) {
            @mkdir(self::$dir, 0755, true);
        }
    }

    public static function setTtl(int $seconds): void {
        self::$ttl = max(30, $seconds);
    }

    public static function makeKey(): string {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $parts = parse_url($uri);
        $path = $parts['path'] ?? '/';
        $query = $parts['query'] ?? '';
        $lang = $_SESSION['lang'] ?? 'en';
        return md5($path . '?' . $query . '|lang=' . $lang);
    }

    public static function get(): ?string {
        if (!self::$dir) self::init();
        if (self::isLoggedIn()) return null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') return null;
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') return null;

        $key = self::makeKey();
        $file = self::$dir . '/' . $key . '.html';

        if (!file_exists($file)) return null;
        if (time() - filemtime($file) > self::$ttl) {
            @unlink($file);
            return null;
        }
        return file_get_contents($file);
    }

    public static function set(string $html): void {
        if (!self::$dir) self::init();
        if (self::isLoggedIn()) return;

        $key = self::makeKey();
        $file = self::$dir . '/' . $key . '.html';
        file_put_contents($file, $html, LOCK_EX);
    }

    public static function flush(): void {
        if (!self::$dir) self::init();
        $files = glob(self::$dir . '/*.html');
        foreach ($files as $f) {
            @unlink($f);
        }
    }

    private static function isLoggedIn(): bool {
        return !empty($_SESSION['id_user']);
    }
}

// Simple in-memory query result cache (per request)
class QueryCache {
    private static array $store = [];

    public static function remember(string $key, callable $callback, int $ttl = 60): mixed {
        $k = md5($key);
        if (isset(self::$store[$k])) {
            $entry = self::$store[$k];
            if (time() - $entry['time'] < $ttl) {
                return $entry['data'];
            }
        }
        $data = $callback();
        self::$store[$k] = ['data' => $data, 'time' => time()];
        return $data;
    }

    public static function flush(): void {
        self::$store = [];
    }
}
