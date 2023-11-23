<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\Interface;

use Inpsyde\Assets\Style;
use Kaiseki\WordPress\Vite\ViteServerInterface;

interface StyleFilterInterface
{
    public function __invoke(?Style $style, ViteServerInterface $viteClient): ?Style;
}
