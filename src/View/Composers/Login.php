<?php

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

namespace Kleinweb\SamlAuth\View\Composers;

use Kleinweb\SamlAuth\SamlAuth;
use Roots\Acorn\View\Composer;

final class Login extends Composer
{
    /** @var string[] */
    // phpcs:ignore SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint -- Would result in error.
    protected static $views = [
        SamlAuth::VIEW_PREFIX . 'partials.login-form.cta',
    ];

    /**
     * @return string[]
     */
    protected function with(): array
    {
        return [
            'ctaText' => $this->ctaText,
            'ctaUrl' => $this->ctaUrl(),
        ];
    }

    public string $ctaText = 'Log in with TU AccessNet';

    public function ctaUrl(): string
    {
        return SamlAuth::loginUrl();
    }
}
