<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite;

use function content_url;
use function defined;
use function dirname;
use function str_replace;
use function strpos;
use function trailingslashit;

/**
 * @phpstan-import-type ChunkData from ChunkInterface
 */
class ViteManifestFile
{
    private string $assetBasePath;
    private ?string $assetBaseUrl;
    private ChunkBuilder $chunkBuilder;

    /**
     * @param string                   $manifestPath
     * @param array<string, ChunkData> $data
     */
    public function __construct(
        public readonly string $manifestPath,
        public readonly array $data,
    ) {
        $this->assetBasePath = trailingslashit(dirname($this->manifestPath));
        $this->assetBaseUrl = $this->buildAssetBaseUrl($this->manifestPath);
        $this->chunkBuilder = new ChunkBuilder();
    }

    public function getAssetBasePath(): string
    {
        return $this->assetBasePath;
    }

    public function getAssetBaseUrl(): ?string
    {
        return $this->assetBaseUrl;
    }

    /**
     * @param string $fileName
     *
     * @return ChunkInterface|null
     */
    public function getChunkByFileName(string $fileName): ?ChunkInterface
    {
        foreach ($this->data as $chunkName => $chunkData) {
            if (isset($chunkData['file']) && $chunkData['file'] === $fileName) {
                return $this->chunkBuilder->build($chunkName, $chunkData);
            }
        }

        return null;
    }

    public function isValid(): bool
    {
        return $this->assetBaseUrl !== null;
    }

    private function buildAssetBaseUrl(string $manifestPath): ?string
    {
        if (!defined('WP_CONTENT_DIR')) {
            return null;
        }

        // Get the WordPress content directory absolute path
        $contentDir = WP_CONTENT_DIR;

        // Get the WordPress content directory URL
        $contentUrl = content_url();

        // remove .vite/ from absolute path
        // https://vitejs.dev/guide/migration.html#manifest-files-are-now-generated-in-vite-directory-by-default
        $absolutePath = str_replace('.vite/', '', $manifestPath);

        // Check if the absolute path contains the content directory path
        if (strpos($absolutePath, $contentDir) === false) {
            return null;
        }

        // Replace the content directory path with the content URL
        $url = str_replace($contentDir, $contentUrl, $absolutePath);

        // Replace backslashes with forward slashes for Windows compatibility
        $url = str_replace('\\', '/', $url);

        // Return the url without the manifest file name
        return trailingslashit(dirname($url));
    }
}
