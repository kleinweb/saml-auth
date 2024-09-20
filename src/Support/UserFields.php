<?php

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
