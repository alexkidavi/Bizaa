<?php

use Illuminate\Support\Facades\Route;

Route::fallback(function () {
    return response()->json([
        'path' => request()->path(),
        'method' => request()->method(),
        'route' => request()->route()?->uri(),
        'route_name' => request()->route()?->getName(),
    ]);
});