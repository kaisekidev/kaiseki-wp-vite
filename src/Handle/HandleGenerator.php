<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\Handle;

use Kaiseki\WordPress\Vite\ChunkInterface;

use function pathinfo;

use const PATHINFO_FILENAME;

final class HandleGenerator implements HandleGeneratorInterface
{
    public function __construct(private readonly string $prefix = '')
    {
    }

    public function generate(ChunkInterface $chunk, string $resource): string
    {
        return $this->prefix . pathinfo($chunk->getSourceFileName(), PATHINFO_FILENAME);
    }
}
