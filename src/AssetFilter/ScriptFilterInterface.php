<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\AssetFilter;

use Inpsyde\Assets\Script;
use Kaiseki\WordPress\Vite\ViteServerInterface;

interface ScriptFilterInterface
{
    public function __invoke(?Script $script, ViteServerInterface $viteClient): ?Script;
}
