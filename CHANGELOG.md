# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 1.1.0 - 2026-11-03

### What's Changed

* chore(ci): bump create-github-app-token to v3 in notify-site by @wolfgangschaefer in https://github.com/kaisekidev/kaiseki-wp-vite/pull/5
* chore(ci): adopt notify-site reusable workflow and harden changelog caller by @wolfgangschaefer in https://github.com/kaisekidev/kaiseki-wp-vite/pull/6
* chore(deps): adopt area:* labels and trim dead dependabot ignores by @wolfgangschaefer in https://github.com/kaisekidev/kaiseki-wp-vite/pull/7
* feat: add forBlockEditorCanvasLocation() to asset filters by @davidmondok in https://github.com/kaisekidev/kaiseki-wp-vite/pull/10

### New Contributors

* @davidmondok made their first contribution in https://github.com/kaisekidev/kaiseki-wp-vite/pull/10

**Full Changelog**: https://github.com/kaisekidev/kaiseki-wp-vite/compare/1.0.0...1.1.0

## 1.0.0 - 2026-06-01

First tagged release, migrated onto the kaiseki PHP 8.4 baseline.

### Changed

- BC: raise the PHP floor to `^8.2` (was `^8.1`).
- Pin internal kaiseki dependencies to tagged releases: `kaiseki/config ^2.0`, `kaiseki/wp-hook ^2.0`,
  `kaiseki/wp-env ^1.0` (were `dev-master`).
- Modernize the dev toolchain: PHPStan `^2.0` (`phpstan`, `phpstan-phpunit`, `phpstan-strict-rules`),
  `szepeviktor/phpstan-wordpress ^2.0`, PHPUnit `^11.0`, `bnf/phpstan-psr-container ^1.1`,
  `phpstan/extension-installer ^1.4`, and `kaiseki/php-coding-standard ^1.0`.
- Add `maglnet/composer-require-checker ^4.0` and a `check-deps` script (the package shipped a
  `require-checker.config.json` but never wired up the checker).
- Adopt the shared PHPStan config, the thin reusable CI caller, Dependabot, and the changelog
  automation workflow.

### Fixed

- BC: `Config::addScriptsFilter()` / `Config::addStylesFilter()` now append the given filters to the
  flat filter list instead of nesting the entire prior list as a single array element — the previous
  behaviour produced a malformed `script_filter` / `style_filter` config. Callers relying on the
  broken nested shape will see corrected output.
- PHPStan level-max findings fixed at the root (no suppression): narrow `inpsyde/assets`
  `Asset` to `FilterAwareAsset` / `DataAwareAsset` via `instanceof` (replacing `method_exists`
  guards), narrow the deprecated location/dependency filters' return type to `Asset`, runtime-narrow
  the untrusted Vite manifest JSON in `Chunk` / `ViteManifestFile` / `ViteManifestLoader`, and coerce
  the registered asset list with `array_values()`.
