# kaiseki/wp-vite

Companion module for [inpsyde/assets](https://github.com/inpsyde/assets) that registers the
scripts and stylesheets emitted by a [Vite](https://vitejs.dev/) build from its `manifest.json`,
with transparent hot-module-reload support against the Vite dev server.

It wires three `kaiseki/wp-hook` `HookProviderInterface`s through `ConfigProvider`:

- **`ViteAssetManager`** — reads each configured Vite manifest, turns every entry chunk (and its
  CSS imports) into an `inpsyde/assets` `Script`/`Style`, and registers them on `AssetManager::ACTION_SETUP`.
- **`ViteClientScriptRenderer`** — injects the `@vite/client` HMR script into `wp_head`/`admin_head`
  when the dev server is hot.
- **`OutputFilterRegistry`** — registers the configured `inpsyde/assets` output filters.

## Installation

```bash
composer require kaiseki/wp-vite
```

Requires PHP 8.2 or newer.

## Usage

Register `ConfigProvider` with your laminas-style config aggregator and provide the `vite` config
slice. The `Config` builder offers a fluent API for assembling that slice:

```php
use Kaiseki\WordPress\Vite\Config;
use Kaiseki\WordPress\Vite\AssetFilter\ScriptFilter;

$vite = (new Config())
    // Path to a Vite build manifest; pass true for the hot (dev-server) variant.
    ->addManifest(get_stylesheet_directory() . '/dist/.vite/manifest.json')
    // Default filters applied to every script / style asset.
    ->addScriptsFilter(ScriptFilter::create()->forFrontendLocation())
    // Per-entry filter, keyed by the manifest entry name.
    ->addScriptFilter('resources/main.ts', ScriptFilter::create()->withDependencies('jquery'))
    // Vite dev-server connection used for hot-reload detection.
    ->setViteServerHost('localhost')
    ->setViteServerPort(5173)
    ->enableAutoLoad();

return [
    'vite' => $vite->toArray(),
    'hook' => [
        'provider' => [
            \Kaiseki\WordPress\Vite\OutputFilterRegistry::class,
            \Kaiseki\WordPress\Vite\ViteAssetManager::class,
            \Kaiseki\WordPress\Vite\ViteClientScriptRenderer::class,
        ],
    ],
];
```

`ConfigProvider::__invoke()` ships sensible defaults (an empty `vite` config plus the dependency
aliases and factories) — merge it into your aggregator so the providers resolve from the container.
Each asset is loaded from the Vite dev server when it is reachable, and falls back to the built file
URL otherwise.

## Development

```bash
composer install
composer check   # check-deps, cs-check, phpstan
```

## License

MIT — see [LICENSE](LICENSE).
