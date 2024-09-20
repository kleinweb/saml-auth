<?php

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

namespace Kleinweb\SamlAuth\Support;

final class UserFields
{
    public const LOGIN = SamlAttribute::uid;
    public const EMAIL = SamlAttribute::mail;
    public const DISPLAY_NAME = SamlAttribute::displayName;
    public const FIRST_NAME = SamlAttribute::templeEduSn;
    public const LAST_NAME = SamlAttribute::templeEduGivenName;
}
