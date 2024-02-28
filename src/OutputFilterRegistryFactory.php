<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite;

use Inpsyde\Assets\OutputFilter\AssetOutputFilter;
use Kaiseki\Config\Config;
use Psr\Container\ContainerInterface;

final class OutputFilterRegistryFactory
{
    public function __invoke(ContainerInterface $container): OutputFilterRegistry
    {
        $config = Config::fromContainer($container);
        /** @var list<class-string<AssetOutputFilter>> $outputFilterClassStrings */
        $outputFilterClassStrings = $config->array('vite.output_filters');
        /** @var array<string, AssetOutputFilter> $outputFilters */
        $outputFilters = [];
        foreach ($outputFilterClassStrings as $filter) {
            $outputFilters[$filter] = Config::initClass($container, $filter);
        }

        return new OutputFilterRegistry($outputFilters);
    }
}
