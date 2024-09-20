<?php

declare(strict_types=1);

namespace Kleinweb\SamlAuth\Http\Controllers;

use Illuminate\Http\Response as HttpResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;
use Kleinweb\SamlAuth\SamlAuth;
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
