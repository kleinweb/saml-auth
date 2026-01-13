# Kleinweb SAML Authentication

A WordPress SAML 2.0 authentication package for Klein College sites.
Built as an [Acorn][acorn] service provider, it wraps [wp-saml-auth][wpsa]
with custom login page modifications, managed user provisioning,
and bulk user import capabilities.

[acorn]: https://roots.io/acorn/
[wpsa]: https://github.com/pantheon-systems/wp-saml-auth

## Features

- SAML 2.0 Single Sign-On via institutional Identity Provider
- Custom WordPress login page with IdP toggle
- Managed user accounts with SAML attribute mapping
- Bulk user import from CSV
- SP metadata endpoint at `/sp/metadata`

## Requirements

- PHP 8.3+
- WordPress with [Acorn](https://roots.io/acorn/) 5.0+
- [wp-saml-auth](https://github.com/pantheon-systems/wp-saml-auth) plugin

## Installation

```bash
composer require kleinweb/saml-auth
```

The service provider is auto-discovered via Acorn. Configure in `config/kleinweb-auth.php`.

## Configuration

X.509 certificates should be placed in `.config/x509/`:

- `.config/x509/keys/<name>.key` - Private key
- `.config/x509/certs/<name>.crt` - Certificate

Key config options in `config/kleinweb-auth.php`:

- `allow_local_login` - Enable/disable WordPress password login (default: `true`)
- `auto_provision` - Auto-create accounts for new IdP users (default: `false`)
- `default_role` - Role assigned to provisioned users

## Development

```bash
just check    # Run all QA checks
just fix      # Apply formatters and fixers
just build    # Build frontend assets
just dev      # Watch mode
```

## Tools

- <https://www.samltool.com/>

## References

### SAML Specifications

- [Assertions and Protocols for the OASIS Security Assertion Markup Language (SAML) V2.0](http://docs.oasis-open.org/security/saml/v2.0/saml-core-2.0-os.pdf)
- [Glossary for the OASIS Security Assertion Markup Language (SAML) V2.0](http://docs.oasis-open.org/security/saml/v2.0/saml-glossary-2.0-os.pdf)
- [Metadata for the OASIS Security Assertion Markup Language (SAML) V2.0](http://docs.oasis-open.org/security/saml/v2.0/saml-metadata-2.0-os.pdf)
- [Profiles for the OASIS Security Assertion Markup Language (SAML) V2.0](http://docs.oasis-open.org/security/saml/v2.0/saml-profiles-2.0-os.pdf)

### SAML Attribute Naming Conventions

- [SAML V2.0 X.500/LDAP Attribute Profile](http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-attribute-x500.pdf)
- [Attribute Naming - Shibboleth Concepts](https://shibboleth.atlassian.net/wiki/spaces/CONCEPT/pages/928645306/AttributeNaming#AttributeNaming-SAMLNamingConventions)
- [eduPerson Object Class Specification (20220208) v4.4.0](https://github.com/REFEDS/eduperson/blob/master/eduperson-202208.md)
- [`eduPersonDisplayPronouns`](https://wiki.refeds.org/display/STAN/eduPersonDisplayPronouns)

#### Background

- <https://www.rfc-editor.org/rfc/rfc2798.html>
- <https://www.rfc-editor.org/rfc/rfc4519.html>
- <https://www.rfc-editor.org/rfc/rfc4524.html>
