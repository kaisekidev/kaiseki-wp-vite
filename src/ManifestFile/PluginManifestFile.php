<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\ManifestFile;

use Kaiseki\WordPress\Vite\ViteServerInterface;

final class PluginManifestFile extends AbstractManifestFile implements ManifestFileInterface
{
    public function getDirectoryUrl(ViteServerInterface $viteServer): string
    {
        return $viteServer->isHot()
            ? $viteServer->getServerUrl()
            : trailingslashit(plugin_dir_url($this->file)) . ltrim(trailingslashit($this->outDir), '/\\');
    }
}
