<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite;

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
            'hook' => [
                'provider' => [
                    ViteAssetManager::class,
                    ViteClientScriptRenderer::class,
                ],
            ],
            'dependencies' => [
                'aliases' => [
                    HandleGeneratorInterface::class => HandleGenerator::class,
                    ViteServerInterface::class => ViteServer::class,
                ],
                'factories' => [
                    HandleGenerator::class          => HandleGeneratorFactory::class,
                    ViteAssetManager::class         => ViteAssetManagerFactory::class,
                    ViteClientScriptRenderer::class => ViteClientScriptRendererFactory::class,
                    ViteManifestLoader::class       => ViteManifestLoaderFactory::class,
                    ViteServer::class               => ViteServerFactory::class,
                ],
            ],
        ];
    }
}
