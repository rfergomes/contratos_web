<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Alertas de vencimento diários às 06:00 AM
\Illuminate\Support\Facades\Schedule::command('contracts:send-expiry-alerts')->dailyAt('06:00');
