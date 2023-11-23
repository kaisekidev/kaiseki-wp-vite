<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite;

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
                    ViteAssetsRegistry::class,
                    ViteClientScriptRenderer::class,
                ],
            ],
            'dependencies' => [
                'aliases' => [
                    ViteServerInterface::class => ViteServer::class,
                ],
                'factories' => [
                    ViteAssetsRegistry::class => ViteAssetsRegistryFactory::class,
                    ViteClientScriptRenderer::class => ViteClientScriptRendererFactory::class,
                    ViteServer::class => ViteServerFactory::class,
                ],
            ],
        ];
    }
}
