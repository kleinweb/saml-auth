<?php

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

namespace Kleinweb\Auth\Http\Controllers;

use Illuminate\Http\Response as HttpResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;
use Kleinweb\Auth\SamlAuth;
use OneLogin\Saml2\Error;

final class SamlAuthController extends Controller
{
    /**
     * @throws Error
     */
    public function metadata(SamlAuth $auth): HttpResponse
    {
        return Response::make($auth->metadata(), 200, ['Content-Type' => 'text/xml']);
    }
}
