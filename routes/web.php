<?php

use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'BookStore Backend is running'
    ]);
});

