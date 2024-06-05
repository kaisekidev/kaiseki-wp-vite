<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\Handle;

use Kaiseki\WordPress\Vite\ChunkInterface;

interface HandleGeneratorInterface
{
    /**
     * @param ChunkInterface $chunk
     * @param string         $resource
     *
     * @return string
     */
    public function generate(ChunkInterface $chunk, string $resource): string;
}
