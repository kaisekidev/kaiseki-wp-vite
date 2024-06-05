<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\AssetFilter;

use Inpsyde\Assets\Style;
use Kaiseki\WordPress\Vite\ChunkInterface;

interface StyleFilterInterface
{
    /**
     * @param Style          $style*
     * @param ChunkInterface $chunk
     *
     * @return Style|null
     */
    public function __invoke(Style $style, ChunkInterface $chunk): ?Style;
}
