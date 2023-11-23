<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\AssetFilter;

use Inpsyde\Assets\Script;
use Kaiseki\WordPress\Vite\ViteServerInterface;

final class ScriptFilterPipeline implements ScriptFilterInterface
{
    /** @var array<AssetFilterInterface|ScriptFilterInterface> */
    private array $filter;

    public function __construct(ScriptFilterInterface|AssetFilterInterface ...$filter)
    {
        $this->filter = $filter;
    }

    public function __invoke(?Script $script, ViteServerInterface $viteClient): ?Script
    {
        foreach ($this->filter as $filter) {
            $script = ($filter)($script, $viteClient);
        }
        // @phpstan-ignore-next-line
        return $script;
    }
}
