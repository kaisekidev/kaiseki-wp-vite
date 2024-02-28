<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\AssetFilter;

use Inpsyde\Assets\Asset;

/**
 * @phpstan-import-type Chunk from \Kaiseki\WordPress\Vite\ViteManifestLoader
 */
final class BlockEditorAssetsLocationFilter implements AssetFilterInterface
{
    /**
     * @param Asset  $asset
     * @param string $chunkName
     * @param Chunk  $chunk
     *
     * @return Asset|null
     */
    public function __invoke(Asset $asset, string $chunkName, array $chunk): ?Asset
    {
        return $asset->forLocation(Asset::BLOCK_EDITOR_ASSETS);
    }
}
