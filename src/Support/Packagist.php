<?php

namespace Nox\Framework\Support;

use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class Packagist
{
    private static string $baseUrl = 'https://packagist.org';

    private static string $baseRepositoryUrl = 'https://repo.packagist.org/p2';

    private static int $poolChunkSize = 10;

    public static function search(
        string $query,
        array $tags,
        int $page,
        int $perPage
    ): array
    {
        return Http::asJson()
            ->get(static::$baseUrl . '/search.json', [
                'q' => $query,
                'tags' => $tags,
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
                $responses[] = static::poolPackages($chunk->all());
            });

        return collect(array_merge(...$responses))
            ->map(static fn(Response $response, $key) => $response->json('packages.' . $key . '.0'))
            ->filter()
            ->all();
    }

    private static function poolPackages(array $packages): array
    {
        return Http::pool(static function (Pool $pool) use ($packages) {
            foreach ($packages as $package) {
                $pool->as($package)->asJson()->get(static::$baseRepositoryUrl . '/' . $package . '.json');
            }
        });
    }
}
