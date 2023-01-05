<?php

use Nox\Framework\Settings\Drivers\SettingsFileDriver;
use Nox\Framework\Settings\Facades\Settings;

it('returns the default driver', function () {
    expect(Settings::driver())->toBeInstanceOf(SettingsFileDriver::class);
});
