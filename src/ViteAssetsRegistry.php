<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\AssetManager;
use Inpsyde\Assets\OutputFilter\AssetOutputFilter;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;
use Kaiseki\WordPress\Hook\HookCallbackProviderInterface;
use Kaiseki\WordPress\Vite\AssetFilter\AssetFilterInterface;
use Kaiseki\WordPress\Vite\AssetFilter\ScriptFilterInterface;
use Kaiseki\WordPress\Vite\AssetFilter\StyleFilterInterface;
use Kaiseki\WordPress\Vite\DirectoryUrl\DirectoryUrlInterface;
use Kaiseki\WordPress\Vite\Loader\ViteManifestLoader;
use Kaiseki\WordPress\Vite\ManifestFileLoader\ManifestFileLoaderInterface;
use Kaiseki\WordPress\Vite\OutputFilter\ModuleTypeScriptOutputFilter;

use function add_action;
use function array_map;
use function array_merge;
use function array_reduce;
use function count;
use function preg_quote;

/**
 * @phpstan-type DirectoryUrlCallback callable(ViteClient $viteClient): string
 */
class ViteAssetsRegistry implements HookCallbackProviderInterface
{
    private readonly ViteManifestLoader $loader;

    /**
     * @param ViteServerInterface                           $viteServer
     * @param list<ManifestFileLoaderInterface|string|null> $viteManifests
     * @param ?ScriptFilterInterface                        $scriptFilter
     * @param array<string, bool|ScriptFilterInterface>     $scripts
     * @param ?StyleFilterInterface                         $styleFilter
     * @param array<string, bool|StyleFilterInterface>      $styles
     * @param bool                                          $autoload
     * @param ?DirectoryUrlInterface                        $directoryUrl
     * @param string                                        $handlePrefix
     * @param bool                                          $esModules
     * @param array<string, AssetOutputFilter>                                         $outputFilters
     */
    public function __construct(
        private readonly ViteServerInterface $viteServer,
        private readonly array $viteManifests,
        private readonly ?ScriptFilterInterface $scriptFilter = null,
        private readonly array $scripts = [],
        private readonly ?StyleFilterInterface $styleFilter = null,
        private readonly array $styles = [],
        private readonly bool $autoload = true,
        ?DirectoryUrlInterface $directoryUrl = null,
        private readonly string $handlePrefix = '',
        private readonly bool $esModules = true,
        private readonly array $outputFilters = []
    ) {
        $this->loader = new ViteManifestLoader();

        if ($directoryUrl !== null) {
            $this->loader->withDirectoryUrl(($directoryUrl)($this->viteServer));
        }

        if ($handlePrefix === '') {
            return;
        }

        $this->loader->withHandlePrefix($handlePrefix);
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

        $assets = array_map(
            /** @phpstan-ignore-next-line */
            fn (Asset $asset): Asset => $asset->disableAutodiscoverVersion(),
            $this->loadAssets()
        );

        $filteredAsset = $this->filterAssets($assets);

        if (count($filteredAsset) === 0) {
            return;
        }

        $assetManager->register(...$filteredAsset);
    }

    /**
     * @return list<Asset>
     */
    protected function loadAssets(): array
    {
        $assets = [];

        foreach ($this->viteManifests as $viteManifest) {
            if ($viteManifest instanceof ManifestFileLoaderInterface) {
                $viteManifest = $viteManifest($this->viteServer);
            }

            if ($viteManifest === null) {
                continue;
            }

            $assets = array_merge($assets, $this->loader->load($viteManifest));
        }

        return $assets;
    }

    /**
     * @param list<Asset> $assets
     *
     * @return list<Asset>
     */
    protected function filterAssets(array $assets): array
    {
        return array_reduce(
            $assets,
            function (array $carry, Asset $asset): array {
                $filteredAsset = $this->filterAsset($asset);

                if ($filteredAsset !== null) {
                    $carry[] = $filteredAsset;
                }

                return $carry;
            },
            []
        );
    }

    /**
     * Filter asset.
     *
     * @param Asset $asset
     *
     * @return Asset|null
     */
    private function filterAsset(Asset $asset): ?Asset
    {
        if ($asset instanceof Script) {
            return $this->filterScript($asset);
        }
        if ($asset instanceof Style) {
            return $this->filterStyle($asset);
        }
        return null;
    }

    private function filterScript(Script $script): ?Script
    {
        $handle = $script->handle();

        if ($this->scriptFilter !== null) {
            $script = ($this->scriptFilter)($script, $this->viteServer);
        }

        if ($script === null) {
            return null;
        }

        if ($this->esModules) {
            $script->withFilters(ModuleTypeScriptOutputFilter::class);
        }

        $filter = $this->getScriptFilter($handle);

        if (!($filter instanceof ScriptFilterInterface) && !($filter instanceof AssetFilterInterface)) {
            if ($this->autoload === true && $filter !== false) {
                return $script;
            }
            if ($this->autoload === false && $filter === true) {
                return $script;
            }
            return  null;
        }

        return $filter($script, $this->viteServer);
    }

    private function getScriptFilter(string $handle): ScriptFilterInterface|AssetFilterInterface|bool|null
    {
        if (isset($this->scripts[$handle])) {
            return $this->scripts[$handle];
        }

        $handleWithoutPrefix = $this->getHandleWithoutPrefix($handle);
        if (isset($this->scripts[$handleWithoutPrefix])) {
            return $this->scripts[$handleWithoutPrefix];
        }


        return null;
    }

    private function filterStyle(Style $style): ?Style
    {
        $handle = $style->handle();

        if ($this->styleFilter !== null) {
            $style = ($this->styleFilter)($style, $this->viteServer);
        }

        if ($style === null) {
            return null;
        }

        $filter = $this->getStyleFilter($handle);

        if (!($filter instanceof StyleFilterInterface) && !($filter instanceof AssetFilterInterface)) {
            if ($this->autoload === true && $filter !== false) {
                return $style;
            }
            if ($this->autoload === false && $filter === true) {
                return $style;
            }
            return  null;
        }

        return $filter($style, $this->viteServer);
    }

    private function getStyleFilter(string $handle): StyleFilterInterface|AssetFilterInterface|bool|null
    {
        if (isset($this->styles[$handle])) {
            return $this->styles[$handle];
        }

        $handleWithoutPrefix = $this->getHandleWithoutPrefix($handle);
        if (isset($this->styles[$handleWithoutPrefix])) {
            return $this->styles[$handleWithoutPrefix];
        }


        return null;
    }

    private function getHandleWithoutPrefix(string $handle): string
    {
        return \Safe\preg_replace(
            '/^' . preg_quote($this->handlePrefix, '/') . '/',
            '',
            $handle
        );
    }
}
