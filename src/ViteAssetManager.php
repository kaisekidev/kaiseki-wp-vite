<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\AssetManager;
use Kaiseki\WordPress\Hook\HookProviderInterface;
use Kaiseki\WordPress\Vite\ManifestFileLoader\ManifestFileLoaderInterface;

use function add_action;
use function array_merge;

class ViteAssetManager implements HookProviderInterface
{
    /**
     * @param list<ManifestFileLoaderInterface|string|null> $manifests
     * @param ViteManifestLoader                            $loader
     * @param ViteServerInterface                           $server
     */
    public function __construct(
        private readonly array $manifests,
        private readonly ViteManifestLoader $loader,
        private readonly ViteServerInterface $server
    ) {
    }

    public function addHooks(): void
    {
        add_action(AssetManager::ACTION_SETUP, [$this, 'registerAssets']);
    }

    /**
     * Hook callback to register assets.
     *
     * @param AssetManager $assetManager
     */
    public function registerAssets(AssetManager $assetManager): void
    {
        $assets = $this->loadAssets();

        if ($assets === []) {
            return;
        }

        $assetManager->register(...$assets);
    }

    /**
     * @return list<Asset>
     */
    private function loadAssets(): array
    {
        $assets = [];

        foreach ($this->manifests as $manifest) {
            if ($manifest instanceof ManifestFileLoaderInterface) {
                $manifest = $manifest($this->server);
            }

            if ($manifest === null) {
                continue;
            }

            $assets = array_merge($assets, $this->loader->load($manifest));
        }

        return $assets;
    }
}
