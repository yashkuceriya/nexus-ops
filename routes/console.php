<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('pm:generate')->daily()->at('06:00')->withoutOverlapping();
Schedule::command('cx:weekly-digest')->weeklyOn(1, '07:00')->withoutOverlapping();
