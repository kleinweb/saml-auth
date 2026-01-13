# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Kleinweb SAML Auth is a WordPress authentication package that integrates SAML 2.0 single sign-on. It is built as a Laravel/Acorn service provider that wraps and extends the `pantheon-systems/wp-saml-auth` plugin with custom WordPress login page modifications and user management features.

## Development Commands

### Build and Development

```bash
just build          # Build frontend assets (vite)
just dev            # Watch mode for frontend development
npm run build       # Direct vite build
npm run dev         # Direct vite dev server
```

### Quality Assurance

```bash
just check          # Full QA: biome, php-lint, php-cs-fixer, phpcs, phpstan
just lint           # Linting only (non-stylistic issues)
just fix            # Apply all formatter and fixer changes
just fmt            # Apply safe formatter changes only (treefmt)
```

### PHP-Specific

```bash
composer lint       # Run phpcs + phpstan
composer fix        # Run php-cs-fixer fix + phpcbf
composer phpstan    # Static analysis (PHPStan level 8)
composer phpcs      # Code style checking
just php reindex    # Rebuild Phpactor index
```

### Release

```bash
just release release    # Prepare and tag release (uses cog/cocogitto)
just release bump OLD NEW  # Bump version numbers across manifests
```

## Architecture

### Service Provider Pattern

The package follows Laravel's service provider pattern via Acorn:

- `AuthServiceProvider` (`src/AuthServiceProvider.php`) is the main entry point, registered in `composer.json` under `extra.acorn.providers`
- Uses `kleinweb/lib` PackageServiceProvider base class with attribute-based WordPress hooks (`#[Action]`, `#[Filter]`)

### Key Components

- **Auth** (`src/Auth.php`): Core authentication facade with config helpers and SAML metadata generation
- **SamlAuthPluginAdapter** (`src/SamlAuthPluginAdapter.php`): Bridge to the underlying wp-saml-auth plugin
- **ManagedUser** (`src/ManagedUser.php`): WordPress user management for SAML-authenticated users
- **ImportUsers** (`src/ImportUsers/`): Bulk user import feature

### Namespace and Autoloading

- PHP namespace: `Kleinweb\Auth\`
- PSR-4 autoloading from `src/`
- Blade component namespace: `kleinweb-auth` (e.g., `<x-kleinweb-auth::component />`)
- View namespace: `kleinweb-auth::` (e.g., `view('kleinweb-auth::partials.login-form.cta')`)

### Frontend

- Vite-based build with Laravel/Roots plugins
- Entry points in `resources/js/` and `resources/css/`
- Output to `resources/dist/` with SRI manifest

### Configuration

- Main config: `config/kleinweb-auth.php`
- Config prefix in code: `kleinweb-auth.` (accessed via `Config::get('kleinweb-auth.key')`)
- X.509 certificates expected in `.config/x509/`

### Routes

Routes are defined in `routes/routes.php` using Laravel's Router facade:

- `GET /sp/metadata` - SAML SP metadata endpoint

## Code Standards

- PHPStan level 8 with bleeding edge and WordPress extensions
- PHP-CS-Fixer + PHPCS with `kleinweb/php-coding-standards`
- Biome for JS/TS/CSS/JSON
- REUSE-compliant licensing (SPDX headers required)
- PHP 8.3+ required

## Environment

The project uses Nix flakes for development environment. Key environment variables:

- `PRJ_ROOT` / `PRJ_ROOT_DIR` - Project root directory
- `DDEV_PRIMARY_URL` - DDEV URL for Vite dev server
