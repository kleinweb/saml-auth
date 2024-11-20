# Changelog
All notable changes to this project will be documented in this file. See [conventional commits](https://www.conventionalcommits.org/) for commit guidelines.

- - -
## [1.0.0-rc.1](https://github.com/kleinweb/saml-auth/compare/d1378875daba453df49ad534ab0596c2c0d1689b..1.0.0-rc.1) - 2024-11-20
#### Bug Fixes
- **(assets)** always use main site for package url base - ([d3ca98f](https://github.com/kleinweb/saml-auth/commit/d3ca98f8e7f2676f0a1d4bdaf4cf5cd4b119078e)) - [@montchr](https://github.com/montchr)
- **(login-form)** toggle `disabled` attribute for `#user_pass` - ([13ab3fb](https://github.com/kleinweb/saml-auth/commit/13ab3fbbb5ab61367b9a13bbf63dcbfad7d56c8a)) - [@montchr](https://github.com/montchr)
- **(prj)** remove unnecessary steps from release process - ([1988fdd](https://github.com/kleinweb/saml-auth/commit/1988fdd0cc3a35243446b859f82664a315d5613f)) - [@montchr](https://github.com/montchr)
- **(prj:reuse|views)** avoid adding spdx headers to template files - ([97b6201](https://github.com/kleinweb/saml-auth/commit/97b620122a929ea96fd50546361fe19610225cf9)) - [@montchr](https://github.com/montchr)
- simplify cert reading + use correct paths - ([808cf36](https://github.com/kleinweb/saml-auth/commit/808cf3626d68a5085faaef3022bd2326dcd205be)) - [@montchr](https://github.com/montchr)
- use correct security settings based on config - ([e9542a8](https://github.com/kleinweb/saml-auth/commit/e9542a86dc4b260796198b03f2deb1f08a8fad91)) - [@montchr](https://github.com/montchr)
- boot login/logout controllers - ([7a505e8](https://github.com/kleinweb/saml-auth/commit/7a505e8f4401454fc957aba5ec752c189c66a1da)) - [@montchr](https://github.com/montchr)
- `use` of `Login` view composer overriding same-namespace `Login` - ([355216a](https://github.com/kleinweb/saml-auth/commit/355216a33940d2ff34b855db1d53fdcaf6e47c11)) - [@montchr](https://github.com/montchr)
- make `Logout` readonly class to match `Login` - ([cedc5a6](https://github.com/kleinweb/saml-auth/commit/cedc5a6f4f4061e0d678c417844d9a3e8ca1152a)) - [@montchr](https://github.com/montchr)
- make Login/Logout classes use Hookable - ([a55d0a6](https://github.com/kleinweb/saml-auth/commit/a55d0a661c127c81306c1ed29aa56df487dfd0df)) - [@montchr](https://github.com/montchr)
- phpstan - ([66d9b21](https://github.com/kleinweb/saml-auth/commit/66d9b217752ec5c803a55ae26f4a856235b54b99)) - [@montchr](https://github.com/montchr)
- update css class name - ([b547dfa](https://github.com/kleinweb/saml-auth/commit/b547dfaf3c54209cffdc3184713ee41515d1cf0e)) - [@montchr](https://github.com/montchr)
- use correct config keys - ([0d5da7b](https://github.com/kleinweb/saml-auth/commit/0d5da7bc96c109a2a9ca69bf55d5c77835b736d4)) - [@montchr](https://github.com/montchr)
#### Features
- login form static assets - ([9d56a36](https://github.com/kleinweb/saml-auth/commit/9d56a36e1492ac08e1db63f002dea8476c99e0f9)) - [@montchr](https://github.com/montchr)
- implement custom login form - ([a2fee92](https://github.com/kleinweb/saml-auth/commit/a2fee922623d6d29d63d5812356915eb142d4225)) - [@montchr](https://github.com/montchr)
- absorb wp-saml-auth plugin - ([167ecd8](https://github.com/kleinweb/saml-auth/commit/167ecd8c5dbae90ca513205def78c58589845fbe)) - [@montchr](https://github.com/montchr)
#### Miscellaneous Chores
- **(deps)** update dev-only - ([3128b5d](https://github.com/kleinweb/saml-auth/commit/3128b5d21fbe9f8126a3e82e25d79138fa1a6a84)) - renovate[bot]
- **(deps)** update dependency @biomejs/biome to v1.9.4 - ([3044371](https://github.com/kleinweb/saml-auth/commit/3044371fdba28eadcbea250e2fb5aa30d403951f)) - renovate[bot]
- **(lint)** prevent biome/typescript conflict - ([20633ec](https://github.com/kleinweb/saml-auth/commit/20633ec08b25ac6164aa95cd783bad28584d624a)) - [@montchr](https://github.com/montchr)
- **(login-form)** cleanup - ([321e007](https://github.com/kleinweb/saml-auth/commit/321e00755102a8e3ac92f62104c4efe23afe5c4b)) - [@montchr](https://github.com/montchr)
- lint - ([9a2ced3](https://github.com/kleinweb/saml-auth/commit/9a2ced3ae0e4db40f46d2111fc05f94c529e6552)) - [@montchr](https://github.com/montchr)
- composer update - ([d8fa335](https://github.com/kleinweb/saml-auth/commit/d8fa3355567b11e21f83e986d8bc65e0349215f5)) - [@montchr](https://github.com/montchr)
- update reuse settings - ([31edc13](https://github.com/kleinweb/saml-auth/commit/31edc130008df8c2942fcc5fb2ce9b83b2bf531c)) - [@montchr](https://github.com/montchr)
- rename to `kleinweb-auth` - ([195d103](https://github.com/kleinweb/saml-auth/commit/195d103dc5fbc901b666b23174e5747598080122)) - [@montchr](https://github.com/montchr)
- initial commit - ([d137887](https://github.com/kleinweb/saml-auth/commit/d1378875daba453df49ad534ab0596c2c0d1689b)) - [@montchr](https://github.com/montchr)
#### Refactoring
- use the plugin - ([1668299](https://github.com/kleinweb/saml-auth/commit/1668299c69abf241ccc54f3f1dcbf006f08fd179)) - [@montchr](https://github.com/montchr)
- reduce complexity of authn handler - ([aecf3b7](https://github.com/kleinweb/saml-auth/commit/aecf3b7a4d30154374291eaaeea3e862b428f303)) - [@montchr](https://github.com/montchr)
- rename many classes - ([bf6e783](https://github.com/kleinweb/saml-auth/commit/bf6e7833bf306d2f7ba79a9fd4c886947eb8c5ce)) - [@montchr](https://github.com/montchr)
- use hook attributes - ([098204d](https://github.com/kleinweb/saml-auth/commit/098204d1b534c6f35eed8df49661a6abbe83b03b)) - [@montchr](https://github.com/montchr)
- mv namespace `Kleinweb\SamlAuth` to `Kleinweb\Auth` - ([0f93898](https://github.com/kleinweb/saml-auth/commit/0f93898ef0ddac575724e43b06a52b158c82e995)) - [@montchr](https://github.com/montchr)

- - -

Changelog generated by [cocogitto](https://github.com/cocogitto/cocogitto).