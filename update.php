<?php

use Illuminate\Support\Facades\Artisan;

Artisan::call('vendor:publish', [
    '--tag' => 'health-config',
]);
