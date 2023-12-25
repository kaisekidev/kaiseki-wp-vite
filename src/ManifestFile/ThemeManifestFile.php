<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\ManifestFile;

use Kaiseki\WordPress\Vite\ViteServerInterface;

final class ThemeManifestFile extends AbstractManifestFile implements ManifestFileInterface
{
    public function getDirectoryUrl(ViteServerInterface $viteServer): string
    {
        return $viteServer->isHot()
            ? $viteServer->getServerUrl()
            : trailingslashit(get_stylesheet_directory_uri()) . ltrim(trailingslashit($this->outiDir), '/\\');
    }
}
