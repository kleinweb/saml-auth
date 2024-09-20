<?php

declare(strict_types=1);

namespace Kleinweb\SamlAuth\Support;

/**
 * SAML attribute mapping from FriendlyName to Name.
 */
final class SamlAttribute
{
    public const cn = 'urn:oid:2.5.4.3';
    public const displayName = 'urn:oid:2.16.840.1.113730.3.1.241';
    public const eduPersonAffiliation = 'urn:oid:1.3.6.1.4.1.5923.1.1.1.1';
    public const eduPersonPrimaryAffiliation = 'urn:oid:1.3.6.1.4.1.5923.1.1.1.5';
    public const eduPersonPrincipalName = 'urn:oid:1.3.6.1.4.1.5923.1.1.1.6';
    public const eduPersonScopedAffiliation = 'urn:oid:1.3.6.1.4.1.5923.1.1.1.9';
    public const employeeNumber = 'urn:oid:2.16.840.1.113730.3.1.3';
    public const givenName = 'urn:oid:2.5.4.42';
    public const mail = 'urn:oid:0.9.2342.19200300.100.1.3';
    public const sn = 'urn:oid:2.5.4.4';
    public const templeEduGivenName = 'urn:oid:1.3.6.1.4.1.44987.1.1.2.1.24';
    public const templeEduSn = 'urn:oid:1.3.6.1.4.1.44987.1.1.2.1.25';
    public const uid = 'urn:oid:0.9.2342.19200300.100.1.1';
}
