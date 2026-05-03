<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('php artisan app:sync-spotify-dashboard')->weekly();