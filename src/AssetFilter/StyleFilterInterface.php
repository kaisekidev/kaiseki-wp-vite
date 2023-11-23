<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\AssetFilter;

use Inpsyde\Assets\Style;
use Kaiseki\WordPress\Vite\ViteServerInterface;

interface StyleFilterInterface
{
    public function __invoke(?Style $style, ViteServerInterface $viteClient): ?Style;
}
