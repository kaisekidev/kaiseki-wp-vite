<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\Handle;

/**
 * @phpstan-import-type Chunk from \Kaiseki\WordPress\Vite\ViteManifestLoader
 */
interface HandleGeneratorInterface
{
    /**
     * @param string $chunkName
     * @param Chunk  $chunk
     * @param string $resource
     *
     * @return string
     */
    public function generate(string $chunkName, array $chunk, string $resource): string;
}
