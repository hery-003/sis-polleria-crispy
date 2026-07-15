<?php

use App\Providers\AppServiceProvider;
use App\Providers\AuthServiceProvider;
use App\Providers\EventServiceProvider;
use Laravel\Pennant\PennantServiceProvider;
use Laravel\Pulse\PulseServiceProvider;

return [
    AppServiceProvider::class,
    AuthServiceProvider::class,
    PulseServiceProvider::class,
    PennantServiceProvider::class,
];
