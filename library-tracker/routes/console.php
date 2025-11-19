<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;

Schedule::command('app:remind-users-of-due-books')->dailyAt("23:00");
