<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\ManifestFile;

use Kaiseki\WordPress\Vite\ViteServerInterface;

interface ManifestFileInterface
{
    /**
     * Returns the absolute path to the manifest file.
     *
     * @param \Kaiseki\WordPress\Vite\ViteServerInterface $viteServer
     *
     * @return string|null
     */
    public function getManifestPath(ViteServerInterface $viteServer): ?string;

    /**
     * Returns the URL to the root directory containing the asset files.
     *
     * @param \Kaiseki\WordPress\Vite\ViteServerInterface $viteServer
     *
     * @return string
     */
    public function getDirectoryUrl(ViteServerInterface $viteServer): string;

    /**
     * @param string $directoryUrl
     *
     * @return static
     */
    public function withDirectoryUrl(string $directoryUrl): self;
}
