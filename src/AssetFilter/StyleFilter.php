<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\AssetFilter;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\OutputFilter\AsyncStyleOutputFilter;
use Inpsyde\Assets\Style;
use Kaiseki\WordPress\Vite\ChunkInterface;

class StyleFilter extends AbstractAssetFilter implements AssetFilterInterface
{
    /** @var array<string, array<string, string>> */
    protected array $cssVars = [];

    /** @var string[] */
    protected array $inlineStyles = [];

    protected ?string $media = null;

    /**
     * @return self
     */
    public static function create(): self
    {
        return new self();
    }

    public function __invoke(Asset $asset, ChunkInterface $chunk): ?Asset
    {
        if (!$asset instanceof Style) {
            return $asset;
        }

        $asset = $this->prepareAsset($asset);

        foreach ($this->cssVars as $element => $vars) {
            $asset->withCssVars($element, $vars);
        }

        foreach ($this->inlineStyles as $inline) {
            $asset->withInlineStyles($inline);
        }

        if ($this->media !== null) {
            $asset->forMedia($this->media);
        }

        return $asset;
    }

    /**
     * @param string $media
     *
     * @return self
     */
    public function forMedia(string $media): self
    {
        $this->media = $media;

        return $this;
    }

    /**
     * @param string $inline
     *
     * @return self
     */
    public function withInlineStyles(string $inline): self
    {
        $this->inlineStyles[] = $inline;

        return $this;
    }

    /**
     * @param string                $element
     * @param array<string, string> $vars
     *
     * @return self
     */
    public function withCssVars(string $element, array $vars): self
    {
        if (!isset($this->cssVars[$element])) {
            $this->cssVars[$element] = [];
        }

        foreach ($vars as $key => $value) {
            $this->cssVars[$element][$key] = $value;
        }

        return $this;
    }

    public function useAsyncFilter(): self
    {
        $this->withFilters(AsyncStyleOutputFilter::class);

        return $this;
    }
}
