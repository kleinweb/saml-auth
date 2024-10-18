<?php

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

namespace Kleinweb\Auth;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Kleinweb\Auth\Entities\IdP;
use Kleinweb\Auth\Entities\SP;
use Kleinweb\Lib\Tenancy\Site;

final class SamlToolkitSettings
{
    final public const SECURITY_DEFAULTS = [
        'authnRequestsSigned' => false,
        // TODO: make this configurable
        // FIXME: for testing only!  disabled temporarily by OIAM for
        // the kleinforms staging environment.
        // 'wantAssertionsSigned' => true,
        // 'wantAssertionsEncrypted' => true,
        'wantMessagesSigned' => true,
        'wantNameId' => true,
        'wantNameIdEncrypted' => false,
        'wantXMLValidation' => true,
    ];

    final public const ORGANIZATION_DEFAULT = [
        'name' => 'Klein College of Media and Communication',
        'displayname' => 'Klein College of Media and Communication',
        'url' => 'https://klein.temple.edu',
    ];

    /**
     * @var Collection<string, (bool|string)|array<bool|string>>
     */
    public readonly Collection $settings;

    public function __construct()
    {
        $this->settings = $this->collect();
    }

    /**
     * @return mixed|null
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->settings->get($key, $default);
    }

    /**
     * @return array<bool|string>|array<array<bool|string>>
     */
    public function make(): array
    {
        return $this->settings->toArray();
    }

    /**
     * @return Collection<string, mixed>
     */
    public function collect(): Collection
    {
        $settings = Collection::make(
            [
                'strict' => true,
                'debug' => SamlAuth::isDebugEnabled(),
                'baseurl' => Site::url()->toString(),
                'sp' => SP::config(),
                'idp' => IdP::config(),
                'contactPerson' => $this->contactPerson(),
                'organization' => [
                    'en-US' => self::organization(),
                ],
            ],
        );

        if (!SamlAuth::isDangerouslyInsecure()) {
            $settings->put('security', self::SECURITY_DEFAULTS);
        }

        return $settings;
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function contactPerson(): array
    {
        $contact = Config::array(SamlAuth::CONFIG_PREFIX . 'contact');
        $mapDefaultContact = static fn ($v): array => ($v === 'default')
            ? $contact['default']
            : $v;

        return Collection::make($contact)
            ->forget('default')
            ->map($mapDefaultContact)
            ->toArray();
    }

    /**
     * @return array<string, string>
     */
    public static function organization(): array
    {
        return Config::array(SamlAuth::CONFIG_PREFIX . 'organization', self::ORGANIZATION_DEFAULT);
    }
}
