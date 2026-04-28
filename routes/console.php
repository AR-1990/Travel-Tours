<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Schedule unified subscription sync for all platforms (Web/Authorize.Net, Google Play, Apple)
Schedule::command('subscriptions:sync-all')
    ->daily()
    ->at('05:00')
    ->timezone('America/Los_Angeles'); // Irvine, California timezone

// Schedule renewal reminder emails (runs 5 minutes after subscription sync)
Schedule::command('subscriptions:send-renewal-reminders')
    ->daily()
    ->at('05:05')
    ->timezone('America/Los_Angeles'); // Irvine, California timezone

// Schedule billing history sync (runs 10 minutes after subscription sync)
// This syncs actual transaction history and billing events from all platforms
Schedule::command('subscriptions:sync-billing-history')
    ->daily()
    ->at('05:10')
    ->timezone('America/Los_Angeles'); // Irvine, California timezone
