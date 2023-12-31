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
use Kaiseki\WordPress\Vite\Handle\HandleGeneratorInterface;
use Psr\Container\ContainerInterface;

use function array_map;
use function class_exists;
use function is_bool;
use function is_string;

/**
 * @phpstan-type ScriptFilterTypes ScriptFilterInterface|AssetFilterInterface
 * @phpstan-type ScriptFilterPipelineType list<class-string<ScriptFilterTypes>|ScriptFilterTypes>
 * @phpstan-type StyleFilterTypes StyleFilterInterface|AssetFilterInterface
 * @phpstan-type StyleFilterPipelineType list<class-string<StyleFilterTypes>|StyleFilterTypes>
 */
final class ViteManifestLoaderFactory
{
    public function __invoke(ContainerInterface $container): ViteManifestLoader
    {
        $config = Config::get($container);

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

        return new ViteManifestLoader(
            $scriptFilter === [] ? null : new ScriptFilterPipeline(...Config::initClassMap($container, $scriptFilter)),
            $scripts,
            $styleFilter === [] ? null : new StyleFilterPipeline(...Config::initClassMap($container, $styleFilter)),
            $styles,
            $this->getDirectoryUrl($container),
            $config->bool('vite/disable_autoload', false),
            $container->get(HandleGeneratorInterface::class),
        );
    }

    private function getDirectoryUrl(ContainerInterface $container): string
    {
        $config = Config::get($container);

        /** @var ViteServerInterface $server */
        $server = $container->get(ViteServerInterface::class);

        /** @var class-string<DirectoryUrlInterface>|DirectoryUrlInterface|string|null $directoryUrl */
        $directoryUrl = $config->get('vite/directory_url', '', true);

        if ($directoryUrl === null) {
            return '';
        }

        if (is_string($directoryUrl) && class_exists($directoryUrl)) {
            /** @var DirectoryUrlInterface $directoryUrl */
            $directoryUrl = Config::initClass($container, $directoryUrl);
        }

        if ($directoryUrl instanceof DirectoryUrlInterface) {
            return ($directoryUrl)($server);
        }

        return $directoryUrl;
    }
}
