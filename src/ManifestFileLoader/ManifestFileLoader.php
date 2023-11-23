<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\ManifestFileLoader;

use Kaiseki\WordPress\Vite\ViteServerInterface;

final class ManifestFileLoader implements ManifestFileLoaderInterface
{
    public function __construct(private readonly string $path, private readonly bool $isHot = false)
    {
    }

    public function __invoke(ViteServerInterface $viteClient): ?string
    {
        return $viteClient->isHot() === $this->isHot ? $this->path : null;
    }
}
