<?php

use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn (): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View => view('welcome'));

Route::get('/health', HealthController::class);
