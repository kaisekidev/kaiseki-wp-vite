<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\ManifestFileLoader;

use Kaiseki\WordPress\Vite\ViteServerInterface;

interface ManifestFileLoaderInterface
{
    public function __invoke(ViteServerInterface $viteClient): ?string;
}
