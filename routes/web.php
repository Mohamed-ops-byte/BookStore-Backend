<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return [
        'status' => 'ok',
        'message' => 'BookStore Backend is running'
    ];
});

