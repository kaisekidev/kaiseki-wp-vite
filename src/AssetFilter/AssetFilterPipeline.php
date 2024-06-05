<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\AssetFilter;

use Inpsyde\Assets\Asset;
use Kaiseki\WordPress\Vite\ChunkInterface;

final class AssetFilterPipeline implements AssetFilterInterface
{
    /** @var array<AssetFilterInterface|ScriptFilterInterface|StyleFilterInterface> */
    private array $filter;

    public function __construct(ScriptFilterInterface|StyleFilterInterface|AssetFilterInterface ...$filter)
    {
        $this->filter = $filter;
    }

    /**
     * @param Asset          $asset
     * @param ChunkInterface $chunk
     *
     * @return Asset|null
     */
    public function __invoke(Asset $asset, ChunkInterface $chunk): ?Asset
    {
        foreach ($this->filter as $filter) {
            if ($asset === null) {
                return null;
            }
            /** @phpstan-var Asset|null $asset */
            $asset = ($filter)($asset, $chunk);
        }

        return $asset;
    }
}
