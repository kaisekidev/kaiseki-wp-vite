<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\AssetFilter;

use Inpsyde\Assets\Asset;
use Kaiseki\WordPress\Vite\ChunkInterface;

/**
 * @deprecated use ScriptFilter::create()->forLocation(int $location) instead
 */
final class LocationFilter implements AssetFilterInterface
{
    public function __construct(private readonly int $location)
    {
    }

    /**
     * @param Asset          $asset
     * @param ChunkInterface $chunk
     *
     * @return Asset|null
     */
    public function __invoke(Asset $asset, ChunkInterface $chunk): ?Asset
    {
        return $asset->forLocation($this->location);
    }
}
