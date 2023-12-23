<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\AssetFilter;

use Inpsyde\Assets\Script;

/**
 * @phpstan-import-type Chunk from \Kaiseki\WordPress\Vite\ViteManifestLoader
 */
final class ScriptFilterPipeline implements ScriptFilterInterface
{
    /** @var array<AssetFilterInterface|ScriptFilterInterface> */
    private array $filter;

    public function __construct(ScriptFilterInterface|AssetFilterInterface ...$filter)
    {
        $this->filter = $filter;
    }

    public function __invoke(Script $script, string $chunkName, array $chunk): ?Script
    {
        foreach ($this->filter as $filter) {
            if ($script === null) {
                return null;
            }
            /** @phpstan-var Script|null $script */
            $script = ($filter)($script, $chunkName, $chunk);
        }
        return $script;
    }
}
