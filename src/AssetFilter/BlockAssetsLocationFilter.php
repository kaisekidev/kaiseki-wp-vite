<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\AssetFilter;

use Inpsyde\Assets\Asset;
use Kaiseki\WordPress\Vite\ChunkInterface;

/**
 * @deprecated Use ScriptFilter::create()->forBlockAssetsLocation() instead.
 */
final class BlockAssetsLocationFilter implements AssetFilterInterface
{
    /**
     * @param Asset          $asset
     * @param ChunkInterface $chunk
     *
     * @return Asset|null
     */
    public function __invoke(Asset $asset, ChunkInterface $chunk): ?Asset
    {
        return $asset->forLocation(Asset::BLOCK_ASSETS);
    }
}
