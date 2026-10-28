<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('reminder:daily')
    ->dailyAt('17:00')
    ->timezone(config('app.timezone', 'Asia/Jakarta'))
    ->withoutOverlapping(10);
