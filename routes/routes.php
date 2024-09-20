<?php

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Kleinweb\SamlAuth\Http\Controllers\SamlAuthController;

Route::group([
    'middleware' => 'web',
    'prefix' => '/sp',
], static function () {
    Route::get('/metadata', [SamlAuthController::class, 'metadata']);
});
