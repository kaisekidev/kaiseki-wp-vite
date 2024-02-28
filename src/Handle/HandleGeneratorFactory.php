<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\Handle;

use Kaiseki\Config\Config;
use Psr\Container\ContainerInterface;

final class HandleGeneratorFactory
{
    public function __invoke(ContainerInterface $container): HandleGenerator
    {
        $config = Config::fromContainer($container);
        return new HandleGenerator($config->string('vite.handle_prefix', ''));
    }
}
