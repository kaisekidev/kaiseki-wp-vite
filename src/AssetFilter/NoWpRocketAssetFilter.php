<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\AssetFilter;

use Inpsyde\Assets\Asset;
use Kaiseki\WordPress\Vite\Interface\AssetFilterInterface;
use Kaiseki\WordPress\Vite\ViteServerInterface;

final class NoWpRocketAssetFilter implements AssetFilterInterface
{
    public function __invoke(?Asset $asset, ViteServerInterface $viteClient): ?Asset
    {
        if ($asset === null) {
            return $asset;
        }

        return $asset->withAttributes([
            'nowprocket' => 'true',
        ]);
    }
}
