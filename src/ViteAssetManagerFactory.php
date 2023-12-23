<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\OutputFilter\AssetOutputFilter;
use Kaiseki\Config\Config;
use Kaiseki\WordPress\Vite\AssetFilter\AssetFilterInterface;
use Kaiseki\WordPress\Vite\AssetFilter\ScriptFilterInterface;
use Kaiseki\WordPress\Vite\AssetFilter\StyleFilterInterface;
use Kaiseki\WordPress\Vite\ManifestFileLoader\ManifestFileLoaderInterface;
use Psr\Container\ContainerInterface;

use function array_merge;

/**
 * @phpstan-type ScriptFilterTypes ScriptFilterInterface|AssetFilterInterface
 * @phpstan-type ScriptFilterPipelineType list<class-string<ScriptFilterTypes>|ScriptFilterTypes>
 * @phpstan-type StyleFilterTypes StyleFilterInterface|AssetFilterInterface
 * @phpstan-type StyleFilterPipelineType list<class-string<StyleFilterTypes>|StyleFilterTypes>
 */
final class ViteAssetManagerFactory
{
    public function __invoke(ContainerInterface $container): ViteAssetManager
    {
        $config = Config::get($container);
        /** @var ViteManifestLoader $loader */
        $loader = $container->get(ViteManifestLoader::class);
        /** @var ViteServerInterface $server */
        $server = $container->get(ViteServerInterface::class);
        /** @var list<ManifestFileLoaderInterface|string|null> $manifests */
        $manifests = $config->array('vite/manifests', []);


        /** @var list<class-string<AssetOutputFilter>> $outputFilterClassStrings */
        $outputFilterClassStrings = $config->array('vite/output_filters', []);
        /** @var array<string, AssetOutputFilter> $outputFilters */
        $outputFilters = [];
        foreach ($outputFilterClassStrings as $filter) {
            $outputFilters[$filter] = Config::initClass($container, $filter);
        }

        return new ViteAssetManager(
            $this->loadAssets($manifests, $loader, $server),
            $outputFilters
        );
    }

    /**
     * @param list<ManifestFileLoaderInterface|string|null> $manifests
     * @param ViteManifestLoader                            $loader
     * @param ViteServerInterface                           $server
     *
     * @return list<Asset>
     */
    private function loadAssets(array $manifests, ViteManifestLoader $loader, ViteServerInterface $server): array
    {
        $assets = [];

        foreach ($manifests as $manifest) {
            if ($manifest instanceof ManifestFileLoaderInterface) {
                $manifest = $manifest($server);
            }

            if ($manifest === null) {
                continue;
            }

            $assets = array_merge($assets, $loader->load($manifest));
        }

        return $assets;
    }
}
