<?php declare(strict_types=1);

namespace App;

use Carbon\Carbon;

class Cache
{
    public static function save(string $key, string $data, int $ttl = 120): void
    {
        $cacheFile = '../cache/' . $key;

        file_put_contents($cacheFile, json_encode([
            'expires_at' => Carbon::now()->addSeconds($ttl),
            'data' => $data
        ]));
    }

    public static function delete(string $key): void
    {
        unlink('../cache/' . $key);
    }

    public static function get(string $key): ?string
    {
        if (!self::has($key)) {
            return null;
        }
        $content = json_decode(file_get_contents('../cache/' . $key));

        return $content->data;
    }

    public static function has(string $key): bool
    {
        if (!file_exists('../cache/' . $key)) {
            return false;
        }
        $content = json_decode(file_get_contents('../cache/' . $key));
        return Carbon::now() < Carbon::parse($content->expires_at);
    }
}