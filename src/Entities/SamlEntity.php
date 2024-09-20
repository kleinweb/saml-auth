<?php

declare(strict_types=1);

namespace Kleinweb\SamlAuth\Entities;

use Kleinweb\SamlAuth\Entities\Contracts\SamlEntityMetadata;

abstract class SamlEntity implements SamlEntityMetadata
{
    protected static string $x509Path = ABSPATH . '/.config/sso/';

    public static string $x509Certificate = '';

    /**
     * @return array<string, bool|string>
     */
    abstract public static function config(): array;

    public static function x509Certificate(): string
    {
        if (!static::$x509Certificate) {
            static::$x509Certificate = static::readX509Certificate();
        }

        return static::$x509Certificate;
    }

    abstract protected static function readX509Certificate(): string;
}
