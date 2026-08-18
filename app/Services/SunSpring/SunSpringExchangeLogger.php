<?php

namespace App\Services\SunSpring;

use Illuminate\Support\Facades\File;

class SunSpringExchangeLogger
{
    public static ?string $directory = null;

    public static int $sequence = 0;

    /**
     * @param  array<string, mixed>  $request
     * @param  array<string, mixed>  $response
     */
    public static function record(string $method, string $url, array $request, array $response): void
    {
        if (self::$directory === null || self::$directory === '') {
            return;
        }

        File::ensureDirectoryExists(self::$directory);
        self::$sequence++;
        $n = str_pad((string) self::$sequence, 2, '0', STR_PAD_LEFT);
        $slug = self::slugFromUrl($url);

        $safeRequest = $request;
        unset($safeRequest['api-password'], $safeRequest['password']);

        File::put(
            self::$directory.'/'.$n.'-'.$slug.'-RQ.json',
            json_encode([
                'method' => $method,
                'url' => $url,
                'body' => $safeRequest,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        File::put(
            self::$directory.'/'.$n.'-'.$slug.'-RS.json',
            json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    public static function start(string $directory): void
    {
        self::$directory = rtrim($directory, '/');
        self::$sequence = 0;
        File::ensureDirectoryExists(self::$directory);
    }

    public static function stop(): void
    {
        self::$directory = null;
        self::$sequence = 0;
    }

    protected static function slugFromUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: $url;
        $name = trim((string) basename($path), '/');

        return $name !== '' ? preg_replace('/[^A-Za-z0-9_-]/', '', $name) : 'request';
    }
}
