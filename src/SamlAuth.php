<?php

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

namespace Kleinweb\SamlAuth;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Config;
use Kleinweb\Lib\Hooks\Traits\Hookable;
use League\Uri\Components\Query;
use League\Uri\Uri;
use OneLogin\Saml2\Auth as OneLoginAuth;
use OneLogin\Saml2\Error as OneLoginError;

use const FILTER_SANITIZE_URL;

final class SamlAuth
{
    use Hookable;

    final public const NAME = 'kleinweb-auth';

    final public const SHORT_NAME = 'kleinweb-auth';

    final public const CONFIG_PREFIX = self::SHORT_NAME . '.';

    final public const VIEW_PREFIX = self::SHORT_NAME . '::';

    public function __construct(
        protected Application $app,
        protected OneLoginAuth $provider,
    ) {}

    public static function isDangerouslyInsecure(): bool
    {
        return Config::boolean(
            self::CONFIG_PREFIX . 'dangerously_insecure',
            false,
        );
    }

    public static function isDebugEnabled(): bool
    {
        return Config::boolean(
            self::CONFIG_PREFIX . 'debug',
            Config::boolean('app.debug'),
        );
    }

    public static function isLocalLoginAllowed(): bool
    {
        return Config::boolean(
            self::CONFIG_PREFIX . 'allow_local_login',
            true,
        );
    }

    public static function loginUrl(): string
    {
        $url = Uri::new(wp_login_url());
        $redirectTo = (string) filter_input(INPUT_GET, 'redirect_to', FILTER_SANITIZE_URL);
        $queryArgs = Query::fromUri($url)
            ->withPair('action', 'kleinweb-auth')
            ->withPair('redirect_to', $redirectTo);
        $url = $url->withQuery($queryArgs);

        return $url->toString();
    }

    /**
     * @throws OneLoginError
     */
    public function metadata(): string
    {
        $settings = $this->provider->getSettings();
        $metadata = $settings->getSPMetadata();
        $errors = $settings->validateMetadata($metadata);

        if (count($errors)) {
            $message = 'Invalid SP metadata: ' . implode(', ', $errors);
            // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
            throw new OneLoginError($message, OneLoginError::METADATA_SP_INVALID);
        }

        return $metadata;
    }
}
