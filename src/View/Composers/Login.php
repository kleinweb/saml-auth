<?php

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

namespace Kleinweb\Auth\View\Composers;

use Kleinweb\Auth\SamlAuth;
use Roots\Acorn\View\Composer;

final class Login extends Composer
{
    /** @var string[] */
    // phpcs:ignore SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint -- Would result in error.
    protected static $views = [
        SamlAuth::VIEW_PREFIX . 'partials.login-form.cta',
        SamlAuth::VIEW_PREFIX . 'partials.login-form.idp-toggle',
    ];

    /**
     * @return string[]
     */
    protected function with(): array
    {
        return [
            'ctaText' => $this->ctaText,
            'ctaUrl' => $this->ctaUrl(),
            'idpToggleDefaultText' => $this->idpToggleDefaultText,
        ];
    }

    public string $idpToggleDefaultText = 'Use local account';

    public string $ctaText = 'Log in with TU AccessNet';

    public function ctaUrl(): string
    {
        return SamlAuth::loginUrl();
    }
}
