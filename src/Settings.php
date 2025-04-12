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

final class Settings
{
    final public const DEFAULT_ORG_CONTACT = [
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
        return Collection::make(
            [
                'strict' => true,
                'debug' => Auth::isDebugEnabled(),
                'baseurl' => Site::url()->toString(),
                'sp' => SP::config(),
                'idp' => IdP::config(),
                'contactPerson' => self::contactPerson(),
                'organization' => [
                    'en-US' => self::organization(),
                ],
                'security' => self::security(),
            ],
        );
    }

    /**
     * @return array<string, bool>
     */
    public static function security(): array
    {
        return [
            'authnRequestsSigned' => true,
            'wantAssertionsSigned' => true,
            'wantAssertionsEncrypted' => ! Auth::isDangerouslyInsecure(),
            'wantMessagesSigned' => true,
            'wantNameId' => true,
            'wantNameIdEncrypted' => false,
            'wantXMLValidation' => true,
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    public static function contactPerson(): array
    {
        $contact = Config::array(Auth::CONFIG_PREFIX . 'contact');
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
        return Config::array(Auth::CONFIG_PREFIX . 'organization', self::DEFAULT_ORG_CONTACT);
    }
}
