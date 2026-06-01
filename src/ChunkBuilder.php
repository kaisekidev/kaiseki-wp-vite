<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite;

class ChunkBuilder
{
    /**
     * @param string                  $name
     * @param array<array-key, mixed> $data
     */
    public function build(string $name, array $data): ChunkInterface
    {
        return new Chunk($name, $data);
    }
}
