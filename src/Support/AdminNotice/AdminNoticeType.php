<?php

// SPDX-FileCopyrightText: (C) 2025 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later

declare(strict_types=1);

namespace Kleinweb\Auth\Support\AdminNotice;

enum AdminNoticeType: string
{
    case INFO = 'info';
    case ERROR = 'error';
    case SUCCESS = 'success';
    case WARNING = 'warning';
}
