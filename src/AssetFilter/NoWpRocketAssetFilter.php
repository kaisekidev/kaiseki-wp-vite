<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\AssetFilter;

use Inpsyde\Assets\Asset;
use Kaiseki\WordPress\Vite\ChunkInterface;

use function method_exists;

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
        if (!method_exists($asset, 'withAttributes')) {
            return $asset;
        }

        $withAttributes = $asset->withAttributes([
            'nowprocket' => 'true',
        ]);

        return $withAttributes instanceof Asset ? $withAttributes : $asset;
    }
}
