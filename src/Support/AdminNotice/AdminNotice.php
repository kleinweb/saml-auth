<?php

// SPDX-FileCopyrightText: (C) 2025-2026 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later

declare(strict_types=1);

namespace Kleinweb\Auth\Support\AdminNotice;

final class AdminNotice
{
    public function __construct(
        public AdminNoticeType $type,
        public string $message,
        public bool $isDismissible = true,
    ) {}
}
