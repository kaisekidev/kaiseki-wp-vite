<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Loader\AbstractWebpackLoader;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;
use Kaiseki\WordPress\Vite\AssetFilter\AssetFilterInterface;
use Kaiseki\WordPress\Vite\AssetFilter\ScriptFilterInterface;
use Kaiseki\WordPress\Vite\AssetFilter\StyleFilterInterface;
use Kaiseki\WordPress\Vite\Handle\HandleGeneratorInterface;
use Kaiseki\WordPress\Vite\OutputFilter\ModuleTypeScriptOutputFilter;

use function array_keys;
use function dirname;
use function in_array;
use function is_array;
use function is_string;
use function pathinfo;
use function str_replace;
use function str_starts_with;
use function strpos;

use const PATHINFO_FILENAME;

/**
 * Implementation of Vite manifest.json parsing into Assets.
 *
 * @link https://vitejs.dev/guide/backend-integration.html
 *
 * @phpstan-type Chunk array{
 *     src?: string,
 *     file: string,
 *     css?: array<string>,
 *     assets?: array<string>,
 *     isEntry?: bool,
 *     isDynamicEntry?: bool,
 *     imports?: array<string>,
 *     dynamicImports?: array<string>,
 * }
 */
class ViteManifestLoader extends AbstractWebpackLoader
{
    /**
     * @param ?ScriptFilterInterface                                         $scriptFilter
     * @param array<string, AssetFilterInterface|bool|ScriptFilterInterface> $scriptFilters
     * @param ?StyleFilterInterface                                          $styleFilter
     * @param array<string, AssetFilterInterface|bool|StyleFilterInterface>  $styleFilters
     * @param string                                                         $directoryUrl
     * @param bool                                                           $disableAutoload
     * @param ?HandleGeneratorInterface                                      $handleGenerator
     */
    public function __construct(
        private readonly ?ScriptFilterInterface $scriptFilter = null,
        private readonly array $scriptFilters = [],
        private readonly ?StyleFilterInterface $styleFilter = null,
        private readonly array $styleFilters = [],
        string $directoryUrl = '',
        private readonly bool $disableAutoload = false,
        private readonly ?HandleGeneratorInterface $handleGenerator = null,
    ) {
        if ($directoryUrl !== '') {
            $this->withDirectoryUrl($directoryUrl);
        }

        $this->autodiscoverVersion = false;
    }

    /**
     * {@inheritDoc}
     *
     * @param array<string, mixed> $data
     * @param string               $resource
     *
     * @return list<Asset>
     */
    protected function parseData(array $data, string $resource): array
    {
        $directory = trailingslashit(dirname($resource));
        $url = $this->convertPathToUrl($resource);
        if ($url === null) {
            return [];
        }
        $directoryUrl = trailingslashit(dirname($url));
        $assets = [];
        foreach ($data as $chunkName => $chunk) {
            // It can be possible, that the "handle"-key is a filepath.
            $chunkName = pathinfo($chunkName, PATHINFO_FILENAME);

            if (str_starts_with($chunkName, '_')) {
                continue;
            }

            if (
                !is_array($chunk)
                || !isset($chunk['isEntry'])
                || $chunk['isEntry'] !== true
                || !isset($chunk['file'])
                || !is_string($chunk['file'])
                || $chunk['file'] === ''
            ) {
                continue;
            }

            $handle = $this->handleGenerator !== null
                ? $this->handleGenerator->generate($chunkName, $chunk, $resource)
                : pathinfo($chunkName, PATHINFO_FILENAME);
            $sanitizedFile = $this->sanitizeFileName($chunk['file']);
            $fileUrl = $directoryUrl . $sanitizedFile;
            $filePath = $directory . $sanitizedFile;
            $asset = $this->buildAsset($handle, $fileUrl, $filePath);

            if ($asset === null) {
                continue;
            }

            $filteredAsset = $this->filterAsset($asset, $chunkName, $chunk);

            if ($filteredAsset === null) {
                continue;
            }

            $assets[] = $asset;
        }

        return $assets;
    }

    /**
     * @param string $handle
     * @param string $fileUrl
     * @param string $filePath
     *
     * @return Script|Style|null
     */
    protected function buildAsset(string $handle, string $fileUrl, string $filePath): Script|Style|null
    {
        $extensionsToClass = [
            'css' => Style::class,
            'sass' => Style::class,
            'scss' => Style::class,
            'js' => Script::class,
            'jsx' => Script::class,
            'ts' => Script::class,
            'tsx' => Script::class,
        ];

        /** @var array{filename?:string, extension?:string} $pathInfo */
        $pathInfo = pathinfo($filePath);
        $filename = $pathInfo['filename'] ?? '';
        $extension = $pathInfo['extension'] ?? '';

        if (!in_array($extension, array_keys($extensionsToClass), true)) {
            return null;
        }

        $class = $extensionsToClass[$extension];

        /** @var Script|Style $asset */
        $asset = new $class($handle, $fileUrl, $this->resolveLocation($filename));
        $asset->withFilePath($filePath);
        $asset->canEnqueue(true);

        $this->autodiscoverVersion
            ? $asset->enableAutodiscoverVersion()
            : $asset->disableAutodiscoverVersion();

        if ($asset instanceof Script) {
            $asset->withFilters(ModuleTypeScriptOutputFilter::class);
        }

        return $asset;
    }

    /**
     * Filter asset.
     *
     * @param Script|Style $asset
     * @param string       $chunkName
     * @param Chunk        $chunk
     *
     * @return Script|Style|null
     */
    private function filterAsset(Script|Style $asset, string $chunkName, array $chunk): Script|Style|null
    {
        $handle = $asset->handle();
        $assetFilters = $asset instanceof Script
            ? $this->scriptFilters
            : $this->styleFilters;

        if ($this->scriptFilter !== null && $asset instanceof Script) {
            $asset = ($this->scriptFilter)($asset, $chunkName, $chunk);
        }

        if ($this->styleFilter !== null && $asset instanceof Style) {
            $asset = ($this->styleFilter)($asset, $chunkName, $chunk);
        }

        if ($asset === null) {
            return null;
        }

        $filter = $assetFilters[$handle] ?? $assetFilters[$chunkName] ?? null;

        if (
            $filter instanceof ScriptFilterInterface
            || $filter instanceof StyleFilterInterface
            || $filter instanceof AssetFilterInterface
        ) {
            return $filter($asset, $chunkName, $chunk);
        }

        if ($this->disableAutoload === false && $filter !== false) {
            return $asset;
        }

        if ($this->disableAutoload === true && $filter === true) {
            return $asset;
        }

        return  null;
    }

    private function convertPathToUrl(string $absolutePath): ?string
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
        $absolutePath = str_replace('.vite/', '', $absolutePath);

        // Check if the absolute path contains the content directory path
        if (strpos($absolutePath, $contentDir) !== false) {
            // Replace the content directory path with the content URL
            $url = str_replace($contentDir, $contentUrl, $absolutePath);

            // Replace backslashes with forward slashes for Windows compatibility
            $url = str_replace('\\', '/', $url);

            return $url;
        }

        return null;
    }
}
