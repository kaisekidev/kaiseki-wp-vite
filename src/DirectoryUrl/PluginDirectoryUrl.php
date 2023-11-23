<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\DirectoryUrl;

use Kaiseki\WordPress\Vite\ViteServerInterface;

use function ltrim;

final class PluginDirectoryUrl implements DirectoryUrlInterface
{
    /**
     * @param string $file   the filename of the plugin (__FILE__)
     * @param string $outDir
     */
    public function __construct(
        private readonly string $file,
        private readonly string $outDir = ''
    ) {
    }

    public function __invoke(ViteServerInterface $viteClient): string
    {
        return $viteClient->isHot()
            ? $viteClient->getServerUrl()
            : trailingslashit(plugin_dir_url($this->file)) . ltrim(trailingslashit($this->outDir), '/\\');
    }
}
