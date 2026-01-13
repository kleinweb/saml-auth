<?php

// SPDX-FileCopyrightText: (C) 2025-2026 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later

declare(strict_types=1);

namespace Kleinweb\Auth\ImportUsers;

use WP_Error;

final class Results
{
    public function __construct(
        /** @var Result[] */
        public array $data = [],
        /** @var WP_Error[] */
        public array $errors = [],
    ) {}
}
