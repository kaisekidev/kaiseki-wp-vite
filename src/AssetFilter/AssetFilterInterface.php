<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\AssetFilter;

use Inpsyde\Assets\Asset;
use Kaiseki\WordPress\Vite\ViteServerInterface;

interface AssetFilterInterface
{
    public function __invoke(Asset $asset, ViteServerInterface $viteClient): ?Asset;
}
