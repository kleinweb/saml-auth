<?php

// SPDX-FileCopyrightText: (C) 2025-2026 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later

declare(strict_types=1);

namespace Kleinweb\Auth\View\Components;

use Illuminate\View\View;
use Illuminate\View\Component;

final class AdminNotice extends Component
{
    public function __construct(
        public string $type = 'info',
        public bool $isDismissible = true,
    ) {}

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('kleinweb-auth::components.admin-notice');
    }
}
