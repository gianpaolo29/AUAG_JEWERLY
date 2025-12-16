<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
Schedule::command('pawn:check-due-dates')->dailyAt('09:00');


Schedule::command('gold:sync 180')
    ->dailyAt('08:10')
    ->timezone('Asia/Manila')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/gold-sync.log'));
