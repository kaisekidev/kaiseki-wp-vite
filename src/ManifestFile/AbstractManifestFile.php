<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\ManifestFile;

use Kaiseki\WordPress\Vite\ViteServerInterface;

abstract class AbstractManifestFile implements ManifestFileInterface
{
    protected string $directoryUrl = '';

    public function __construct(
        protected readonly string $manifestPath,
        protected readonly string $outiDir = '',
        protected readonly ?bool $isHot = null)
    {
    }

    public function getManifestPath(ViteServerInterface $viteServer): ?string
    {
        if ($this->isHot === null) {
            return $this->manifestPath;
        }

        return $viteServer->isHot() === $this->isHot ? $this->manifestPath : null;
    }

    /**
     * @param string $directoryUrl
     *
     * @return static
     */
    public function withDirectoryUrl(string $directoryUrl): self
    {
        $this->directoryUrl = $directoryUrl;

        return $this;
    }
}
