<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\AssetFilter;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\OutputFilter\AssetOutputFilter;
use Inpsyde\Assets\OutputFilter\InlineAssetOutputFilter;

use function array_merge;

abstract class AbstractAssetFilter
{
    /** @var array<string, mixed> */
    protected array $attributes = [];

    protected ?string $condition = null;

    /** @var string[] */
    protected array $dependencies = [];

    /** @var bool|callable(): bool */
    protected $enqueue = true;

    /** @var AssetOutputFilter[]|callable[]|class-string<AssetOutputFilter>[] */
    protected array $filters = [];

    protected ?int $location = null;

    protected ?string $version = null;

    abstract public static function create(): self;

    protected function prepareAsset(Asset $asset): Asset
    {
        if ($this->attributes !== [] && method_exists($asset, 'withAttributes')) {
            $asset = $asset->withAttributes($this->attributes);
        }

        if ($this->condition !== null) {
            $asset = $asset->withCondition($this->condition);
        }

        if ($this->dependencies !== []) {
            $asset = $asset->withDependencies(...$this->dependencies);
        }

        if ($this->version !== null) {
            $asset = $asset->withVersion($this->version);
        }

        if ($this->location !== null) {
            $asset = $asset->forLocation($this->location);
        }

        if ($this->enqueue !== null) {
            $asset = $asset->canEnqueue($this->enqueue);
        }

        if ($this->filters !== []) {
            $asset = $asset->withFilters(...$this->filters);
        }

        return $asset;
    }

    public function forLocation(int $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function forFrontendLocation(): self
    {
        return $this->forLocation(Asset::FRONTEND);
    }

    public function forBackendLocation(): self
    {
        return $this->forLocation(Asset::BACKEND);
    }

    public function forBlockAssetsLocation(): self
    {
        return $this->forLocation(Asset::BLOCK_ASSETS);
    }

    public function forBlockEditorLocation(): self
    {
        return $this->forLocation(Asset::BLOCK_EDITOR_ASSETS);
    }

    public function forCustomizerLocation(): self
    {
        return $this->forLocation(Asset::CUSTOMIZER);
    }

    public function forLoginLocation(): self
    {
        return $this->forLocation(Asset::LOGIN);
    }

    /**
     * @param callable|class-string<AssetOutputFilter> ...$filters
     */
    public function withFilters(...$filters): self
    {
        $this->filters = array_merge($this->filters, $filters);

        return $this;
    }

    public function canEnqueue(bool|callable $enqueue): self
    {
        $this->enqueue = $enqueue;

        return $this;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function withAttributes(array $attributes): self
    {
        $this->attributes = array_merge($this->attributes, $attributes);

        return $this;
    }

    public function withCondition(string $condition): self
    {
        $this->condition = $condition;

        return $this;
    }

    public function withDependencies(string ...$dependencies): self
    {
        $this->dependencies = array_merge(
            $this->dependencies,
            $dependencies
        );

        return $this;
    }

    public function withVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function useInlineFilter(): self
    {
        return $this->withFilters(InlineAssetOutputFilter::class);
    }

    public function useNoWpRocketFilter(): self
    {
        $this->withAttributes([
            'nowprocket' => 'true',
        ]);

        return $this;
    }
}
