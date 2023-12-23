<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\AssetManager;
use Inpsyde\Assets\OutputFilter\AssetOutputFilter;
use Kaiseki\WordPress\Hook\HookCallbackProviderInterface;
use Kaiseki\WordPress\Vite\OutputFilter\ModuleTypeScriptOutputFilter;

use function add_action;

class ViteAssetManager implements HookCallbackProviderInterface
{
    /**
     * @param list<Asset>                      $assets
     * @param array<string, AssetOutputFilter> $outputFilters
     */
    public function __construct(
        private readonly array $assets,
        private readonly array $outputFilters = []
    ) {
    }

    public function registerHookCallbacks(): void
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
        $handlers = $assetManager->handlers();

        foreach ($handlers as $handler) {
            /** @phpstan-ignore-next-line */
            $handler->withOutputFilter(ModuleTypeScriptOutputFilter::class, new ModuleTypeScriptOutputFilter());

            foreach ($this->outputFilters as $name => $filter) {
                /** @phpstan-ignore-next-line */
                $handler->withOutputFilter($name, $filter);
            }
        }

        $assetManager->register(...$this->assets);
    }
}
