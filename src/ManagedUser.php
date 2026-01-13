<?php

// SPDX-FileCopyrightText: (C) 2024-2026 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later

declare(strict_types=1);

namespace Kleinweb\Auth;

use Kleinweb\Auth\Support\UserField;
use Kleinweb\Lib\Hooks\Attributes\Action;
use Kleinweb\Lib\Hooks\Attributes\Filter;
use Kleinweb\Lib\Hooks\Traits\Hookable;
use Roots\Acorn\Application;
use Webmozart\Assert\Assert;
use WP_User;
use Kleinweb\Lib\Support\CoreObjects;

use function get_user_meta;
use function update_user_meta;
use function wp_get_current_user;

final class ManagedUser
{
    use Hookable;

    public const USER_META_KEY = 'is_saml_managed';

    /** @var UserField[] */
    public static array $managedFields = [
        UserField::EMAIL,
        UserField::FIRST_NAME,
        UserField::LAST_NAME,
    ];

    public function __construct(private Application $app) {}

    public function boot(): void
    {
        $this->registerHooks();
        $this->injectAssets();
    }

    private function injectAssets(): void
    {
        global $pagenow;

        if ($pagenow !== 'profile.php') {
            return;
        }

        $assets = $this->app->make('assets.kleinweb-auth');
        $assets->inject([
            'resources/js/user-profile.ts',
        ]);
    }

    public static function isManagedUser(int $id): bool
    {
        return (bool) get_user_meta($id, self::USER_META_KEY, single: true);
    }

    #[Filter('show_password_fields')]
    public static function canChangePassword(): bool
    {
        return ! self::isManagedUser(wp_get_current_user()->ID);
    }

    #[Action('wp_saml_auth_existing_user_authenticated')]
    public static function ensureUserMetaOnAuthn(WP_User $user): void
    {
        update_user_meta($user->ID, self::USER_META_KEY, true);
    }

    /**
     * @param array<string, array<int, mixed>> $samlAttrs
     */
    #[Action('wp_saml_auth_existing_user_authenticated')]
    public static function updateExistingUserOnAuthn(WP_User $user, array $samlAttrs): void
    {
        $userArgs = ['ID' => $user->ID];
        $attrs = collect($samlAttrs);

        foreach (self::$managedFields as $field) {
            $attr = $attrs->get($field->samlAttribute());
            $userArgs[$field()] = $attr[0] ?? '';
        }

        wp_update_user($userArgs);
    }

    #[Action('personal_options_update')]
    #[Action('edit_user_profile_update')]
    public static function preventManagedFieldsUpdate(int $userId): void
    {
        if (!self::isManagedUser($userId)) {
            return;
        }

        $user = CoreObjects::getUser($userId);
        Assert::isInstanceOf($user, WP_User::class);

        foreach (self::$managedFields as $field) {
            $param = match ($field) {
                UserField::EMAIL => 'email',
                default => $field(),
            };
            $_POST[$param] = $user->{$field()};
        }
    }
}
