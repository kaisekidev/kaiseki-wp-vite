<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite;

use Kaiseki\WordPress\Vite\AssetFilter\AssetFilterInterface;
use Kaiseki\WordPress\Vite\AssetFilter\ScriptFilterInterface;
use Kaiseki\WordPress\Vite\AssetFilter\StyleFilterInterface;
use Kaiseki\WordPress\Vite\ManifestFileLoader\ManifestFileLoader;
use Kaiseki\WordPress\Vite\ManifestFileLoader\ManifestFileLoaderInterface;

/**
 * @phpstan-type FilterType AssetFilterInterface|ScriptFilterInterface|StyleFilterInterface
 * @phpstan-type ScriptFilterType AssetFilterInterface|ScriptFilterInterface
 * @phpstan-type StyleFilterType AssetFilterInterface|StyleFilterInterface
 */
class Config
{
    /**
     * @var ManifestFileLoaderInterface[]
     */
    private array $manifests = [];
    /**
     * @var array<class-string<ScriptFilterType>|ScriptFilterType>
     */
    private array $scriptFilter = [];
    /**
     * @var array<class-string<StyleFilterType>|StyleFilterType>
     */
    private array $styleFilter = [];
    /**
     * @var array<string, bool|array<class-string<ScriptFilterType>|ScriptFilterType>>
     */
    private array $scripts = [];
    /**
     * @var array<string, bool|array<class-string<StyleFilterType>|StyleFilterType>>
     */
    private array $styles = [];
    /**
     * @var array{host?: string, port?: int}|null
     */
    private ?array $viteServer = null;
    private ?string $handlePrefix = null;
    private ?bool $autoload = null;

    public function addManifest(string $manifestPath, bool $hot = false): self
    {
        $this->manifests[] = new ManifestFileLoader($manifestPath, $hot);

        return $this;
    }

    /**
     * @param class-string<ScriptFilterType>|ScriptFilterType ...$filter
     *
     * @return $this
     */
    public function addScriptsFilter(string|AssetFilterInterface|StyleFilterInterface ...$filter): self
    {
        $this->scriptFilter[] = [
            ...$this->scriptFilter,
            ...$filter
        ];

        return $this;
    }

    /**
     * @param string                      $name
     * @param class-string<ScriptFilterType>|ScriptFilterType ...$filter
     *
     * @return $this
     */
    public function addScriptFilter(string $name, string|AssetFilterInterface|ScriptFilterInterface ...$filter): self
    {
        if (!isset($this->scripts[$name]) || !is_array($this->scripts[$name])) {
            $this->scripts[$name] = [];
        }

        $this->scripts[$name] = [
            ...$this->scripts[$name],
            ...$filter
        ];

        return $this;
    }

    /**
     * @param class-string<StyleFilterType>|StyleFilterType ...$filter
     *
     * @return $this
     */
    public function addStylesFilter(string|AssetFilterInterface|ScriptFilterInterface ...$filter): self
    {
        $this->styleFilter[] = [
            ...$this->styleFilter,
            ...$filter
        ];

        return $this;
    }

    /**
     * @param string                      $name
     * @param class-string<StyleFilterType>|StyleFilterType ...$filter
     *
     * @return $this
     */
    public function addStyleFilter(string $name, string|AssetFilterInterface|StyleFilterInterface ...$filter): self
    {
        if (!isset($this->styles[$name]) || !is_array($this->styles[$name])) {
            $this->styles[$name] = [];
        }

        $this->styles[$name] = [
            ...$this->styles[$name],
            ...$filter
        ];

        return $this;
    }

    public function setViteServerHost(string $host): self
    {
        if ($this->viteServer === null) {
            $this->viteServer = [];
        }

        $this->viteServer['host'] = $host;

        return $this;
    }

    public function setViteServerPort(int $port): self
    {
        if ($this->viteServer === null) {
            $this->viteServer = [];
        }

        $this->viteServer['port'] = $port;

        return $this;
    }

    public function setHandlePrefix(string $prefix): self
    {
        $this->handlePrefix = $prefix;

        return $this;
    }

    public function enableAutoLoad(): self
    {
        $this->autoload = true;

        return $this;
    }

    /**
     * @return array{
     *     manifests: ManifestFileLoaderInterface[],
     *     script_filter: array<class-string<ScriptFilterType>|ScriptFilterType>,
     *     style_filter: array<class-string<StyleFilterType>|StyleFilterType>,
     *     scripts: array<string, bool|array<class-string<ScriptFilterType>|ScriptFilterType>>,
     *     styles: array<string, bool|array<class-string<StyleFilterType>|StyleFilterType>>,
     *     handle_prefix?: string|null,
     *     client?: array{host?: string, port?: int}|null,
     *     autoload?: bool|null,
     * }
     */
    public function toArray(): array
    {
        return array_filter([
            'manifests' => $this->manifests,
            'script_filter' => $this->scriptFilter,
            'style_filter' => $this->styleFilter,
            'scripts' => $this->scripts,
            'styles' => $this->styles,
            'handle_prefix' => $this->handlePrefix,
            'client' => $this->viteServer,
            'autoload' => $this->autoload,
        ], fn($value) => $value !== null);
    }
}
