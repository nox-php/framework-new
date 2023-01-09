<?php

namespace Nox\Framework\Support;

use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;

class Packagist
{
    private static string $baseUrl = 'https://packagist.org';

    private static string $baseRepositoryUrl = 'https://repo.packagist.org/p2';

    private static int $poolChunkSize = 10;

    public static function search(
        string $query,
        string $tag,
        int $page,
        int $perPage
    ): array
    {
        return Http::asJson()
            ->get(static::$baseUrl . '/search.json', [
                'q' => $query,
                'tag' => $tag,
                'page' => $page,
                'per_page' => $perPage
            ])
            ->json();
    }

    public static function packages(array $packages): array
    {
        $responses = [];

        collect($packages)
            ->chunk(static::$poolChunkSize)
            ->each(static function ($chunk) use (&$responses) {
                $responses[] = static::poolPackages($chunk);
            });

        return array_merge(...$responses);
    }

    private static function poolPackages(array $packages): array
    {
        return Http::pool(static function (Pool $pool) use ($packages) {
            foreach ($packages as $package) {
                $pool->as($package)->get(static::$baseRepositoryUrl . '/' . $package . '.json');
            }
        });
    }
}
