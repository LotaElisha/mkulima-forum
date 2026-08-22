<?php

use App\Providers\AppServiceProvider;
use App\Providers\SettingsServiceProvider;

return [
    AppServiceProvider::class,
    // Registered after AppServiceProvider so the database-managed overlay is
    // the last word on any config value it manages.
    SettingsServiceProvider::class,
];
