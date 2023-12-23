<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\AssetFilter;

use Inpsyde\Assets\Style;

/**
 * @phpstan-import-type Chunk from \Kaiseki\WordPress\Vite\ViteManifestLoader
 */
final class StyleFilterPipeline implements StyleFilterInterface
{
    /** @var array<AssetFilterInterface|StyleFilterInterface> */
    private array $filter;

    public function __construct(StyleFilterInterface|AssetFilterInterface ...$filter)
    {
        $this->filter = $filter;
    }

    /**
     * @param Style  $style
     * @param string $chunkName
     * @param Chunk  $chunk
     *
     * @return Style|null
     */
    public function __invoke(Style $style, string $chunkName, array $chunk): ?Style
    {
        foreach ($this->filter as $filter) {
            if ($style === null) {
                return null;
            }
            /** @phpstan-var Style|null $style */
            $style = ($filter)($style, $chunkName, $chunk);
        }
        return $style;
    }
}
