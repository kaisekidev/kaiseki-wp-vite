<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\AssetFilter;

use Inpsyde\Assets\Script;

/**
 * @phpstan-import-type Chunk from \Kaiseki\WordPress\Vite\ViteManifestLoader
 */
interface ScriptFilterInterface
{
    /**
     * @param Script $script
     * @param string $chunkName
     * @param Chunk  $chunk
     *
     * @return Script|null
     */
    public function __invoke(Script $script, string $chunkName, array $chunk): ?Script;
}
