<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite;

use Kaiseki\Config\Config;
use Kaiseki\WordPress\Vite\ManifestFileLoader\ManifestFileLoaderInterface;
use Psr\Container\ContainerInterface;

final class ViteAssetManagerFactory
{
    public function __invoke(ContainerInterface $container): ViteAssetManager
    {
        $config = Config::fromContainer($container);
        $loader = $container->get(ViteManifestLoader::class);
        $server = $container->get(ViteServerInterface::class);
        /** @var list<ManifestFileLoaderInterface|string|null> $manifests */
        $manifests = $config->array('vite.manifests');

        return new ViteAssetManager($manifests, $loader, $server);
    }
}
