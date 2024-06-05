<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\AssetFilter;

use Inpsyde\Assets\Script;
use Kaiseki\WordPress\Vite\ChunkInterface;

interface ScriptFilterInterface
{
    /**
     * @param Script         $script*
     * @param ChunkInterface $chunk
     *
     * @return Script|null
     */
    public function __invoke(Script $script, ChunkInterface $chunk): ?Script;
}
