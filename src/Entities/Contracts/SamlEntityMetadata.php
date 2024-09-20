<?php

declare(strict_types=1);

namespace Kleinweb\SamlAuth\Entities\Contracts;

interface SamlEntityMetadata
{
    public static function entityId(): string;

    public static function fqdn(): string;

    public static function loginUrl(): string;

    public static function logoutUrl(): string;

    public static function x509Certificate(): string;
}
