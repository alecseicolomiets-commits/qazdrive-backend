<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name'    => 'QAZ DRIVE API',
        'version' => '1.0.0',
        'status'  => 'running',
        'docs'    => 'http://127.0.0.1:8000/api',
    ]);
});