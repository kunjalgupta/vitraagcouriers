<?php

use Illuminate\Support\Facades\Route;

Route::get('/', 'LaravelThingsController@returnWelcomeView');
Route::get('/artisan/{cmd}', 'LaravelThingsController@runArtisan');
