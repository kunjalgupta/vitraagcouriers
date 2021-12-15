<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Artisan;

class LaravelThingsController extends Controller
{
    public function anyRoute()
    {
        return response()->json(
            [
                'message' => 'Route Not Found',
                'status' => 0,
                'data' => []
            ],
            404
        );
    }

    public function unAuthenticatedResponse()
    {
        return response()->json(['error' => 'UnAuthenticated'], 401);
    }

    public function returnWelcomeView()
    {
        return view('welcome');
    }

    public function runArtisan($cmd)
    {
        if ($cmd == 'storage-link') {
            Artisan::call('storage:link');
            return '<h1>Storage Linked Successfully</h1>';
        } else if ($cmd == 'optimize') {
            Artisan::call('optimize');
            return '<h1>Reoptimized class loader Successfully</h1>';
        } else if ($cmd == 'cache-config') {
            Artisan::call('config:cache');
            return '<h1>Config cached Successfully</h1>';
        } else if ($cmd == 'cache-route') {
            Artisan::call('route:cache');
            return '<h1>Routes cached Successfully</h1>';
        } else if ('clear-cache') {
            Artisan::call('cache:clear');
            return '<h1>Cache cleared</h1>';
        } else if ('clear-route') {
            Artisan::call('route:clear');
            return '<h1>Route cache cleared</h1>';
        } else if ('clear-config') {
            Artisan::call('config:clear');
            return '<h1>Config cache cleared</h1>';
        }
    }
}
