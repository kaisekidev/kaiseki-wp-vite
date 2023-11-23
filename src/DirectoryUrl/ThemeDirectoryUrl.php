<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\DirectoryUrl;

use Kaiseki\WordPress\Vite\ViteServerInterface;

use function ltrim;

final class ThemeDirectoryUrl implements DirectoryUrlInterface
{
    public function __construct(private readonly string $outDir = '')
    {
    }

    public function __invoke(ViteServerInterface $viteClient): string
    {
        return $viteClient->isHot()
            ? $viteClient->getServerUrl()
            : trailingslashit(get_stylesheet_directory_uri()) . ltrim(trailingslashit($this->outDir), '/\\');
    }
}
