<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite;

/**
 * @phpstan-import-type ChunkData from ChunkInterface
 */
class ChunkBuilder
{
    /**
     * @param string    $name
     * @param ChunkData $data
     */
    public function build(string $name, array $data): ChunkInterface
    {
        return new Chunk($name, $data);
    }
}
