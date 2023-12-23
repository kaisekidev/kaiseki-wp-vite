<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\AssetFilter;

use Inpsyde\Assets\Style;

/**
 * @phpstan-import-type Chunk from \Kaiseki\WordPress\Vite\ViteManifestLoader
 */
interface StyleFilterInterface
{
    /**
     * @param Style  $style
     * @param string $chunkName
     * @param Chunk  $chunk
     *
     * @return Style|null
     */
    public function __invoke(Style $style, string $chunkName, array $chunk): ?Style;
}
