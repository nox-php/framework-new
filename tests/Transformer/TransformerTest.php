<?php

use Nox\Framework\Transformer\Facades\Transformer;

it('can transform data', function (string $key, string $expectedValue) {
    Transformer::register('--first-transformer--', static fn (): string => '--first-transformer-value--');
    Transformer::register('--second-transformer--', static fn (): string => '--second-transformer-value--');

    $value = Transformer::transform($key, null);

    expect($value)->toEqual($expectedValue);
})->with([
    'first transformer' => [
        '--first-transformer--',
        '--first-transformer-value--',
    ],
    'second transformer' => [
        '--second-transformer--',
        '--second-transformer-value--',
    ],
]);

it('can add parameters', function () {
    $key = '--first-transformer--';
    $prefix = '--prefix-';
    $value = 'transformer';
    $suffix = '-suffix--';

    Transformer::register($key, static function (string $prefix, string $value, $suffix) {
        return $prefix.$value.$suffix;
    });

    $transformedValue = Transformer::transform($key, $value, [
        'prefix' => $prefix,
        'suffix' => $suffix,
    ]);

    expect($transformedValue)->toEqual($prefix.$value.$suffix);
});

it('orders transformers', function () {
    $key = '--first-transformer--';

    Transformer::register($key, static fn (): string => '--second-transformer-value--', 2);
    Transformer::register($key, static fn (): string => '--first-transformer-value--');

    $value = Transformer::transform($key, null);

    expect($value)->toEqual('--second-transformer-value--');
});
