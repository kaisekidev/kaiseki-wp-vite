<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Loader\AbstractWebpackLoader;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;
use Kaiseki\WordPress\Vite\AssetFilter\AssetFilterInterface;
use Kaiseki\WordPress\Vite\AssetFilter\ScriptFilterInterface;
use Kaiseki\WordPress\Vite\AssetFilter\StyleFilterInterface;
use Kaiseki\WordPress\Vite\Handle\HandleGeneratorInterface;
use Kaiseki\WordPress\Vite\OutputFilter\ModuleTypeScriptOutputFilter;
use Throwable;

use function array_keys;
use function in_array;
use function is_array;
use function pathinfo;
use function trailingslashit;

use const PATHINFO_FILENAME;

/**
 * @phpstan-import-type ChunkData from ChunkInterface
 */
class ViteManifestLoader extends AbstractWebpackLoader
{
    private ChunkBuilder $chunkBuilder;
    private Client $client;

    /**
     * @param AssetFilterInterface|ScriptFilterInterface|null                $scriptFilter
     * @param array<string, AssetFilterInterface|bool|ScriptFilterInterface> $scriptFilters
     * @param AssetFilterInterface|StyleFilterInterface|null                 $styleFilter
     * @param array<string, AssetFilterInterface|bool|StyleFilterInterface>  $styleFilters
     * @param bool                                                           $autoload
     * @param ?ViteServerInterface                                           $server
     * @param ?HandleGeneratorInterface                                      $handleGenerator
     */
    public function __construct(
        private readonly ScriptFilterInterface|AssetFilterInterface|null $scriptFilter = null,
        private readonly array $scriptFilters = [],
        private readonly StyleFilterInterface|AssetFilterInterface|null $styleFilter = null,
        private readonly array $styleFilters = [],
        private readonly bool $autoload = false,
        private readonly ?ViteServerInterface $server = null,
        private readonly ?HandleGeneratorInterface $handleGenerator = null,
    ) {
        $this->autodiscoverVersion = false;
        $this->chunkBuilder = new ChunkBuilder();
        $this->client = new Client();
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
        // @phpstan-ignore-next-line
        $manifest = new ViteManifestFile($resource, $data);

        if ($manifest->isValid() === false) {
            return [];
        }

        $assets = [];

        foreach ($manifest->data as $chunkName => $chunk) {
            if (!is_array($chunk)) {
                continue;
            }

            $chunk = $this->chunkBuilder->build($chunkName, $chunk);
            if (
                $chunk->isEntry() !== true
                || $chunk->getFile() === ''
            ) {
                continue;
            }

            $asset = $this->assetFromChunk($chunk, $manifest);

            if ($asset === null) {
                continue;
            }

            $assets = [
                ...$assets,
                $asset,
                ...$this->cssFilesToAssets($chunk->getCss(), $chunk, $manifest),
            ];
        }

        return $assets;
    }

    /**
     * @param list<string>     $files
     * @param ViteManifestFile $manifest
     *
     * @return list<Asset>
     */
    private function cssFilesToAssets(
        array $files,
        Chunk $chunk,
        ViteManifestFile $manifest
    ): array {
        $assets = [];

        foreach ($files as $file) {
            $fileChunk = $manifest->getChunkByFileName($file);

            if ($fileChunk === null) {
                $src = str_replace(
                    pathinfo($chunk->getSource(), PATHINFO_EXTENSION),
                    'css',
                    $chunk->getSource()
                );
                $fileChunk = $this->chunkBuilder->build($src, ['file' => $file, 'src' => $src]);
            }

            $asset = $this->assetFromChunk($fileChunk, $manifest);

            if ($asset === null) {
                continue;
            }

            $assets[] = $asset;
        }

        return $assets;
    }

    private function assetFromChunk(
        ChunkInterface $chunk,
        ViteManifestFile $manifest
    ): Asset|null {
        // Generate handle from chunk name or use the handle generator.
        $handle = $this->handleGenerator?->generate($chunk, $manifest->manifestPath)
            ?? $chunk->getSourceFileName();
        $sanitizedFile = $this->sanitizeFileName($chunk->getFile());
        // Try loading the asset from the vite dev server if it exists there.
        $fileUrl = $this->getHotAssetUrl($sanitizedFile) ?? $manifest->getAssetBaseUrl() . $sanitizedFile;
        $filePath = $manifest->getAssetBasePath() . $sanitizedFile;

        $asset = $this->buildAsset($handle, $fileUrl, $filePath);

        if ($asset === null) {
            return null;
        }

        return $this->filterAsset($asset, $chunk);
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
     * @param Script|Style   $asset
     * @param ChunkInterface $chunk
     *
     * @return Asset|null
     */
    private function filterAsset(Script|Style $asset, ChunkInterface $chunk): Asset|null
    {
        $handle = $asset->handle();
        $assetFilters = $asset instanceof Script
            ? $this->scriptFilters
            : $this->styleFilters;

        if ($this->scriptFilter !== null && $asset instanceof Script) {
            $asset = ($this->scriptFilter)($asset, $chunk);
        }

        if ($this->styleFilter !== null && $asset instanceof Style) {
            $asset = ($this->styleFilter)($asset, $chunk);
        }

        if ($asset === null) {
            return null;
        }

        $filter =
            $assetFilters[$handle]
            ?? $assetFilters[$chunk->getSourceFileName()]
            ?? $assetFilters[$chunk->getChunkKey()]
            ?? null;

        if (
            $filter instanceof ScriptFilterInterface
            || $filter instanceof StyleFilterInterface
            || $filter instanceof AssetFilterInterface
        ) {
            return $filter($asset, $chunk);
        }

        if ($this->autoload === true && $filter !== false) {
            return $asset;
        }

        if ($this->autoload === false && $filter === true) {
            return $asset;
        }

        return null;
    }

    private function getHotAssetUrl(string $sanitizedFile): ?string
    {
        if ($this->server?->isHot() !== true) {
            return null;
        }

        $url = trailingslashit($this->server->getServerUrl()) . $sanitizedFile;

        try {
            $statusCode = $this->client
                   ->get(
                       $url,
                       [RequestOptions::HTTP_ERRORS => false]
                   )
                   ->getStatusCode();

            return $statusCode === 200 ? $url : null;
        } catch (Throwable $e) {
        }

        return null;
    }
}
