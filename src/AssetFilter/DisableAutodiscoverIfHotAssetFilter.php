<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\AssetFilter;

use Inpsyde\Assets\Asset;
use Kaiseki\WordPress\Vite\Interface\AssetFilterInterface;
use Kaiseki\WordPress\Vite\ViteServerInterface;

final class DisableAutodiscoverIfHotAssetFilter implements AssetFilterInterface
{
    public function __invoke(?Asset $asset, ViteServerInterface $viteClient): Asset
    {
        /** @phpstan-ignore-next-line */
        return $asset !== null && $viteClient->isHot() ? $asset->disableAutodiscoverVersion() : $asset;
    }
}
