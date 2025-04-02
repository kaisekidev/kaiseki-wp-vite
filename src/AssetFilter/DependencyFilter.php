<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\AssetFilter;

use Inpsyde\Assets\Asset;
use Kaiseki\WordPress\Vite\ChunkInterface;

/**
 * @deprecated Use ScriptFilter::create()->withDependencies(string ...$dependencies) instead.
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
     * @param Asset          $asset
     * @param ChunkInterface $chunk
     *
     * @return Asset|null
     */
    public function __invoke(Asset $asset, ChunkInterface $chunk): ?Asset
    {
        return $asset->withDependencies(...$this->dependencies);
    }
}
