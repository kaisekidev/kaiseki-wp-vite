<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite;

use Kaiseki\WordPress\Environment\Environment;
use Kaiseki\WordPress\Environment\EnvironmentInterface;
use Kaiseki\WordPress\Vite\Handle\HandleGenerator;
use Kaiseki\WordPress\Vite\Handle\HandleGeneratorFactory;
use Kaiseki\WordPress\Vite\Handle\HandleGeneratorInterface;

final class ConfigProvider
{
    /**
     * @return array<mixed>
     */
    public function __invoke(): array
    {
        return [
            'vite' => [
                'manifests' => [],
                'script_filter' => [],
                'scripts' => [],
                'style_filter' => [],
                'styles' => [],
                'disable_autoload' => false,
                'client' => [
                    'host' => 'localhost',
                    'port' => 5173,
                ],
                'output_filters' => [],
            ],
            'hook' => [
                'provider' => [
                    OutputFilterRegistry::class,
                    ViteAssetManager::class,
                    ViteClientScriptRenderer::class,
                ],
            ],
            'dependencies' => [
                'aliases' => [
                    EnvironmentInterface::class => Environment::class,
                    HandleGeneratorInterface::class => HandleGenerator::class,
                    ViteServerInterface::class => ViteServer::class,
                ],
                'factories' => [
                    HandleGenerator::class          => HandleGeneratorFactory::class,
                    OutputFilterRegistry::class          => OutputFilterRegistryFactory::class,
                    ViteAssetManager::class         => ViteAssetManagerFactory::class,
                    ViteClientScriptRenderer::class => ViteClientScriptRendererFactory::class,
                    ViteManifestLoader::class       => ViteManifestLoaderFactory::class,
                    ViteServer::class               => ViteServerFactory::class,
                ],
            ],
        ];
    }
}
