<?php

declare(strict_types=1);

namespace Kleinweb\SamlAuth\PluginIntegration\Contracts;

// phpcs:disable Squiz.NamingConventions

interface WPSamlAuth extends Plugin
{
    public static function get_instance(): object;

    public function get_provider(): mixed;
}
