<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite;

use Inpsyde\Assets\AssetManager;
use Inpsyde\Assets\OutputFilter\AssetOutputFilter;
use Kaiseki\WordPress\Hook\HookProviderInterface;
use Kaiseki\WordPress\Vite\OutputFilter\ModuleTypeScriptOutputFilter;

use function add_action;

class OutputFilterRegistry implements HookProviderInterface
{
    /**
     * @param array<string, AssetOutputFilter> $outputFilters
     */
    public function __construct(private readonly array $outputFilters = [])
    {
    }

    public function addHooks(): void
    {
        add_action(AssetManager::ACTION_SETUP, [$this, 'registerOutputFilters'], 1);
    }

    /**
     * Hook callback to register assets.
     *
     * @param AssetManager $assetManager
     */
    public function registerOutputFilters(AssetManager $assetManager): void
    {
        $handlers = $assetManager->handlers();

        foreach ($handlers as $handler) {
            if (!method_exists($handler, 'withOutputFilter')) {
                continue;
            }

            $handler->withOutputFilter(ModuleTypeScriptOutputFilter::class, new ModuleTypeScriptOutputFilter());

            foreach ($this->outputFilters as $name => $filter) {
                $handler->withOutputFilter($name, $filter);
            }
        }
    }
}
