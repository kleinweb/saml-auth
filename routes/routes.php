<?php

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Kleinweb\Auth\Http\Controllers\SamlAuthController;

Route::group([
    'middleware' => 'web',
    'prefix' => (get_blog_details()->path ?? '/') . 'sp',
], static function () {
    Route::get('/metadata', [SamlAuthController::class, 'metadata']);
});
