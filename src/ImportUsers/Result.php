<?php

// SPDX-FileCopyrightText: (C) 2025 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later

declare(strict_types=1);

namespace Kleinweb\Auth\ImportUsers;

use WP_User;

final class Result
{
    public int $id;
    public string $username;

    public function __construct(
        public WP_User $entity,
        public bool $isNew = false,
        public bool $hasAuthz = false,
        public bool $isExisting = false,
    ) {
        $this->id = $this->entity->ID;
        $this->username = $this->entity->user_login;
    }
}
