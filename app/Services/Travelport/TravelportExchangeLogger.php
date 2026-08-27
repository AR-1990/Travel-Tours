<?php

namespace App\Services\Travelport;

use Illuminate\Support\Facades\File;

/**
 * Writes full SOAP RQ/RS XML for Travelport certification runs.
 */
class TravelportExchangeLogger
{
    public static ?string $directory = null;

    public static int $sequence = 0;

    /**
     * @param  array{ok?: bool, http_status?: ?int, message?: string, body?: string}  $http
     */
    public static function record(string $operation, string $endpoint, string $requestXml, array $http): void
    {
        if (self::$directory === null || self::$directory === '') {
            return;
        }

        File::ensureDirectoryExists(self::$directory);
        self::$sequence++;
        $n = str_pad((string) self::$sequence, 2, '0', STR_PAD_LEFT);
        $slug = preg_replace('/[^A-Za-z0-9_-]/', '', $operation) ?: 'op';

        File::put(self::$directory.'/'.$n.'-'.$slug.'-RQ.xml', $requestXml);
        File::put(
            self::$directory.'/'.$n.'-'.$slug.'-RS.xml',
            (string) ($http['body'] ?? '')
        );
        File::put(
            self::$directory.'/'.$n.'-'.$slug.'-META.json',
            json_encode([
                'operation' => $operation,
                'endpoint' => $endpoint,
                'ok' => (bool) ($http['ok'] ?? false),
                'http_status' => $http['http_status'] ?? null,
                'message' => $http['message'] ?? null,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
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
}
