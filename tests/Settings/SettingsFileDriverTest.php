<?php

use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Nox\Framework\Settings\Drivers\SettingsFileDriver;
use Nox\Framework\Settings\Facades\Settings;
use function Pest\Laravel\get;

it('returns the default value if the key does not exist', function () {
    $key = '--test-key--';
    $defaultValue = '--test-value--';

    expect(Settings::get($key, $defaultValue))->toEqual($defaultValue);
});

it('can set a value', function () {
    $key = '--test-key--';
    $value = '--test-value--';

    Settings::set($key, $value);

    expect(Settings::get($key))->toEqual($value)
        ->and(Settings::has($key))->toBeTrue();
});

it('can set multiple values', function () {
    $data = [
        '--first-test-key--' => '--first-test-value--',
        '--second-test-key--' => '--second-test-value--',
        '--third-test-key--' => '--third-test-value--',
    ];

    Settings::set($data);

    expect(Settings::get())->toEqual($data);
});

it('can check a value exists', function () {
    $key = '--test-key--';
    $value = '--test-value--';

    Settings::set($key, $value);

    expect(Settings::has($key, $value))
        ->toEqual($value);
});

it('can get a value', function () {
    $key = '--test-key--';
    $value = '--test-value--';

    Settings::set($key, $value);

    expect(Settings::get($key))->toEqual($value);
});

it('can pull a value', function () {
    $key = '--test-key--';
    $value = '--test-value--';
    $defaultValue = '--default-value--';

    Settings::set($key, $value);

    expect(Settings::pull($key))->toEqual($value)
        ->and(Settings::pull($key, $defaultValue))->toEqual($defaultValue);
});

it('can forget a value', function () {
    $key = '--test-key--';
    $value = '--test-value--';

    Settings::set($key, $value);

    Settings::forget($key);

    expect(Settings::has($key))->toBeFalse();
});

it('saves the file when terminating', function () {
    $this->mock(SettingsFileDriver::class, function (MockInterface $mock) {
        $mock->shouldReceive('set')->passthru();
        $mock->shouldReceive('save')->passthru()->once();
    });

    $data = [
        '--first-test-key--' => '--first-test-value--',
        '--second-test-key--' => '--second-test-value--',
        '--third-test-key--' => '--third-test-value--',
    ];

    Settings::set($data);

    get('/');

    expect((new SettingsFileDriver())->get())->toEqual($data);
});

it('loads the file from storage', function () {
    $storage = Storage::disk(config('nox.settings.drivers.file.disk'));
    $path = config('nox.settings.drivers.file.path');

    $data = [
        '--first-test-key--' => '--first-test-value--',
        '--second-test-key--' => '--second-test-value--',
        '--third-test-key--' => '--third-test-value--',
    ];

    $storage->put($path, json_encode($data));

    expect((new SettingsFileDriver())->get())->toEqual($data);
});
