<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('spa:open-inventory')->dailyAt('01:00')->timezone('Asia/Jakarta')->withoutOverlapping(30);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
