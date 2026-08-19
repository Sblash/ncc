<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Schedule the command to end expired rounds every minute
Artisan::command('schedule:run', function () {
    Artisan::call('game:end-expired-rounds');
})->everyMinute();
