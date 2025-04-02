<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\AssetFilter;

use Inpsyde\Assets\Asset;
use Kaiseki\WordPress\Vite\ChunkInterface;

/**
 * @deprecated use ScriptFilter::create()->useNoWpRocketFilter() instead
 */
final class NoWpRocketAssetFilter implements AssetFilterInterface
{
    /**
     * @param Asset          $asset
     * @param ChunkInterface $chunk
     *
     * @return Asset|null
     */
    public function __invoke(Asset $asset, ChunkInterface $chunk): ?Asset
    {
        return $asset->withAttributes([
            'nowprocket' => 'true',
        ]);
    }
}
