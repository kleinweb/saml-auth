<?php

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

namespace Kleinweb\SamlAuth\Support;

enum UserField: string
{
    case LOGIN = 'login';
    case EMAIL = 'email';
    case DISPLAY_NAME = 'display_name';
    case FIRST_NAME = 'first_name';
    case LAST_NAME = 'last_name';

    public function name(): string
    {
        return $this->value;
    }

    /**
     * @see <https://github.com/WordPress/WordPress/blob/a8af90bcd1c323cf792c156f355db87767b9f7ab/wp-includes/class-wp-user.php#L223-L243>
     */
    public function dbName(): string
    {
        return match ($this) {
            UserField::EMAIL => 'user_email',
            UserField::LOGIN => 'user_login',
            default => $this->value,
        };
    }

    public function samlAttribute(): string
    {
        return match ($this) {
            UserField::LOGIN => SamlAttribute::uid,
            UserField::EMAIL => SamlAttribute::mail,
            UserField::DISPLAY_NAME => SamlAttribute::displayName,
            UserField::FIRST_NAME => SamlAttribute::templeEduSn,
            UserField::LAST_NAME => SamlAttribute::templeEduGivenName,
        };
    }
}
