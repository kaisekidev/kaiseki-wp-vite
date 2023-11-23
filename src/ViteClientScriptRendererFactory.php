<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite;

use Psr\Container\ContainerInterface;

final class ViteClientScriptRendererFactory
{
    public function __invoke(ContainerInterface $container): ViteClientScriptRenderer
    {
        return new ViteClientScriptRenderer(
            $container->get(ViteServerInterface::class),
        );
    }
}
