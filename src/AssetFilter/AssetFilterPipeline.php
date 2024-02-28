<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\AssetFilter;

use Inpsyde\Assets\Asset;

/**
 * @phpstan-import-type Chunk from \Kaiseki\WordPress\Vite\ViteManifestLoader
 */
final class AssetFilterPipeline implements AssetFilterInterface
{
    /** @var array<AssetFilterInterface|ScriptFilterInterface|StyleFilterInterface> */
    private array $filter;

    public function __construct(ScriptFilterInterface|StyleFilterInterface|AssetFilterInterface ...$filter)
    {
        $this->filter = $filter;
    }

    /**
     * @param Asset  $asset
     * @param string $chunkName
     * @param Chunk  $chunk
     *
     * @return Asset|null
     */
    public function __invoke(Asset $asset, string $chunkName, array $chunk): ?Asset
    {
        foreach ($this->filter as $filter) {
            if ($asset === null) {
                return null;
            }
            /** @phpstan-var Asset|null $asset */
            $asset = ($filter)($asset, $chunkName, $chunk);
        }
        return $asset;
    }
}
