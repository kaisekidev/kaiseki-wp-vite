<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite;

use Kaiseki\Config\Config;
use Kaiseki\WordPress\Vite\AssetFilter\AssetFilterInterface;
use Kaiseki\WordPress\Vite\AssetFilter\AssetFilterPipeline;
use Kaiseki\WordPress\Vite\AssetFilter\ScriptFilterInterface;
use Kaiseki\WordPress\Vite\AssetFilter\StyleFilterInterface;
use Kaiseki\WordPress\Vite\Handle\HandleGeneratorInterface;
use Psr\Container\ContainerInterface;

use function array_map;
use function is_bool;

/**
 * @phpstan-type FilterType AssetFilterInterface|ScriptFilterInterface|StyleFilterInterface
 * @phpstan-type ScriptFilterType AssetFilterInterface|ScriptFilterInterface
 * @phpstan-type StyleFilterType AssetFilterInterface|StyleFilterInterface
 */
final class ViteManifestLoaderFactory
{
    public function __invoke(ContainerInterface $container): ViteManifestLoader
    {
        $config = Config::fromContainer($container);

        /** @var list<class-string<ScriptFilterType>|ScriptFilterType> $scriptFilter */
        $scriptFilter = $config->array('vite.script_filter');
        /** @var array<string, bool|list<class-string<ScriptFilterType>|ScriptFilterType>> $scriptSettings */
        $scriptSettings = $config->array('vite.scripts');

        /** @var list<class-string<StyleFilterType>|StyleFilterType> $styleFilter */
        $styleFilter = $config->array('vite.style_filter');
        /** @var array<string, bool|list<class-string<StyleFilterType>|StyleFilterType>> $styleSettings */
        $styleSettings = $config->array('vite.styles');

        return new ViteManifestLoader(
            $this->initAssetFilterPipeline($scriptFilter, $container),
            $this->initAssetFilterPipelines($scriptSettings, $container),
            $this->initAssetFilterPipeline($styleFilter, $container),
            $this->initAssetFilterPipelines($styleSettings, $container),
            $config->bool('vite.autoload', false),
            $container->get(ViteServerInterface::class),
            $container->get(HandleGeneratorInterface::class),
        );
    }

    /**
     * @param array<string, bool|list<class-string<FilterType>|FilterType>> $filter
     * @param ContainerInterface                                            $container
     *
     * @return array<string, AssetFilterInterface|bool>
     */
    private function initAssetFilterPipelines(
        array $filter,
        ContainerInterface $container
    ): array {
        return array_map(
            fn(bool|array $value): bool|AssetFilterInterface => is_bool($value)
                ? $value
                : $this->initAssetFilterPipeline($value, $container),
            $filter,
        );
    }

    /**
     * @param list<class-string<FilterType>|FilterType> $pipeline
     * @param ContainerInterface                        $container
     *
     * @return AssetFilterInterface
     */
    private function initAssetFilterPipeline(
        array $pipeline,
        ContainerInterface $container
    ): AssetFilterInterface {
        return new AssetFilterPipeline(...Config::initClassMap($container, $pipeline));
    }
}
