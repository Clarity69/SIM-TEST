<?php

use App\Models\ActivityLog;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Bersihkan log aktivitas yang lebih tua dari ActivityLog::RETENSI_HARI (butuh `php artisan schedule:work` / cron).
Schedule::command('model:prune', ['--model' => [ActivityLog::class]])->daily();
