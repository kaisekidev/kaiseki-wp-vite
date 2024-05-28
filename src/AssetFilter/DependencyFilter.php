<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\AssetFilter;

use Inpsyde\Assets\Asset;

/**
 * @phpstan-import-type Chunk from \Kaiseki\WordPress\Vite\ViteManifestLoader
 */
final class DependencyFilter implements AssetFilterInterface
{
    /** @var string[] */
    private array $dependencies;

    public function __construct(string ...$dependencies)
    {
        $this->dependencies = $dependencies;
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
        return $asset->withDependencies(...$this->dependencies);
    }
}
