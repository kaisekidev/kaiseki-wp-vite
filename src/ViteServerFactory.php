<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite;

use Kaiseki\Config\Config;
use Kaiseki\WordPress\Environment\EnvironmentInterface;
use Psr\Container\ContainerInterface;

final class ViteServerFactory
{
    public function __invoke(ContainerInterface $container): ViteServer
    {
        $config = Config::fromContainer($container);
        return new ViteServer(
            $container->get(EnvironmentInterface::class),
            $config->string('vite.client.host'),
            $config->int('vite.client.port'),
        );
    }
}
