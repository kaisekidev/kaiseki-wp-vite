<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\AssetFilter;

use Inpsyde\Assets\Style;
use Kaiseki\WordPress\Vite\Interface\AssetFilterInterface;
use Kaiseki\WordPress\Vite\Interface\StyleFilterInterface;
use Kaiseki\WordPress\Vite\ViteServerInterface;

final class StyleFilterPipeline implements StyleFilterInterface
{
    /** @var array<AssetFilterInterface|StyleFilterInterface> */
    private array $filter;

    public function __construct(StyleFilterInterface|AssetFilterInterface ...$filter)
    {
        $this->filter = $filter;
    }

    public function __invoke(?Style $style, ViteServerInterface $viteClient): ?Style
    {
        foreach ($this->filter as $filter) {
            $style = ($filter)($style, $viteClient);
        }
        // @phpstan-ignore-next-line
        return $style;
    }
}
