<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\AssetFilter;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\FilterAwareAsset;
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
     * @return Asset
     */
    public function __invoke(Asset $asset, ChunkInterface $chunk): Asset
    {
        if (!$asset instanceof FilterAwareAsset) {
            return $asset;
        }

        return $asset->withAttributes([
            'nowprocket' => 'true',
        ]);
    }
}
