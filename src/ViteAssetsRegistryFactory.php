<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite;

use Kaiseki\Config\Config;
use Kaiseki\WordPress\Vite\AssetFilter\AssetFilterInterface;
use Kaiseki\WordPress\Vite\AssetFilter\ScriptFilterInterface;
use Kaiseki\WordPress\Vite\AssetFilter\ScriptFilterPipeline;
use Kaiseki\WordPress\Vite\AssetFilter\StyleFilterInterface;
use Kaiseki\WordPress\Vite\AssetFilter\StyleFilterPipeline;
use Kaiseki\WordPress\Vite\DirectoryUrl\DirectoryUrlInterface;
use Psr\Container\ContainerInterface;

use function array_map;
use function is_bool;

/**
 * @phpstan-type ScriptFilterTypes ScriptFilterInterface|AssetFilterInterface
 * @phpstan-type ScriptFilterPipelineType list<class-string<ScriptFilterTypes>|ScriptFilterTypes>
 * @phpstan-type StyleFilterTypes StyleFilterInterface|AssetFilterInterface
 * @phpstan-type StyleFilterPipelineType list<class-string<StyleFilterTypes>|StyleFilterTypes>
 */
final class ViteAssetsRegistryFactory
{
    public function __invoke(ContainerInterface $container): ViteAssetsRegistry
    {
        $config = Config::get($container);
        /** @var list<string> $manifests */
        $manifests = $config->array('vite/manifests', []);

        /** @var ScriptFilterPipelineType $scriptFilter */
        $scriptFilter = $config->array('vite/script_filter', []);
        /** @var array<string, bool|ScriptFilterPipelineType> $scriptSettings */
        $scriptSettings = $config->array('vite/scripts', []);
        /** @var array<string, bool|ScriptFilterInterface> $scripts */
        $scripts = array_map(
            fn (bool|array $value): bool|ScriptFilterInterface => is_bool($value)
               ? $value
               : new ScriptFilterPipeline(...Config::initClassMap($container, $value)),
            $scriptSettings,
        );

        /** @var StyleFilterPipelineType $styleFilter */
        $styleFilter = $config->array('vite/style_filter', []);
        /** @var array<string, bool|StyleFilterPipelineType> $styleSettings */
        $styleSettings = $config->array('vite/styles', []);
        /** @var array<string, bool|StyleFilterInterface> $styles */
        $styles = array_map(
            fn (bool|array $value): bool|StyleFilterInterface => is_bool($value)
               ? $value
               : new StyleFilterPipeline(...Config::initClassMap($container, $value)),
            $styleSettings,
        );
        /** @var class-string<DirectoryUrlInterface>|DirectoryUrlInterface $directoryUrl */
        $directoryUrl = $config->get('vite/directory_url', '', true);

        return new ViteAssetsRegistry(
            $container->get(ViteServerInterface::class),
            $manifests,
            $scriptFilter === [] ? null : new ScriptFilterPipeline(...Config::initClassMap($container, $scriptFilter)),
            $scripts,
            $styleFilter === [] ? null : new StyleFilterPipeline(...Config::initClassMap($container, $styleFilter)),
            $styles,
            $config->bool('vite/autoload', false),
            Config::initClass($container, $directoryUrl),
            $config->string('vite/handle_prefix', ''),
            $config->bool('vite/es_modules', true),
        );
    }
}
