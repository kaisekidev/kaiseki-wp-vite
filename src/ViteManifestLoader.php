<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Exception\FileNotFoundException;
use Inpsyde\Assets\Exception\InvalidResourceException;
use Inpsyde\Assets\Loader\LoaderInterface;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;
use Kaiseki\WordPress\Vite\AssetFilter\AssetFilterInterface;
use Kaiseki\WordPress\Vite\AssetFilter\ScriptFilterInterface;
use Kaiseki\WordPress\Vite\AssetFilter\StyleFilterInterface;
use Kaiseki\WordPress\Vite\Handle\HandleGeneratorInterface;
use Kaiseki\WordPress\Vite\ManifestFile\ManifestFileInterface;
use Kaiseki\WordPress\Vite\OutputFilter\ModuleTypeScriptOutputFilter;

use function array_keys;
use function dirname;
use function file_get_contents;
use function in_array;
use function is_array;
use function is_readable;
use function is_string;
use function json_decode;
use function json_last_error;
use function ltrim;
use function parse_url;
use function pathinfo;
use function sprintf;
use function str_starts_with;
use function stristr;

use const JSON_ERROR_CTRL_CHAR;
use const JSON_ERROR_DEPTH;
use const JSON_ERROR_STATE_MISMATCH;
use const JSON_ERROR_SYNTAX;
use const JSON_ERROR_UTF8;
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
class ViteManifestLoader implements LoaderInterface
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
     * @param ManifestFileInterface $resource
     *
     * @return array<Asset>
     * @throws FileNotFoundException
     * @throws InvalidResourceException
     */
    public function load($resource): array
    {
        $manifestFile = $this->getManifestPath($resource);
        if (!is_string($manifestFile) || !is_readable($manifestFile)) {
            throw new FileNotFoundException(
                sprintf(
                    'The given file "%s" does not exists or is not readable.',
                    $manifestFile
                )
            );
        }

        $data = @file_get_contents($manifestFile)
            ?: ''; // phpcs:ignore
        $data = json_decode($data, true);
        $errorCode = json_last_error();
        if (0 < $errorCode) {
            throw new InvalidResourceException(
                sprintf('Error parsing JSON - %s', $this->getJSONErrorMessage($errorCode))
            );
        }

        return $this->parseData($data, $resource);
    }

    /**
     * Translates JSON_ERROR_* constant into meaningful message.
     *
     * @param int $errorCode
     *
     * @return string Message string
     */
    private function getJSONErrorMessage(int $errorCode): string
    {
        switch ($errorCode) {
            case JSON_ERROR_DEPTH:
                return 'Maximum stack depth exceeded';
            case JSON_ERROR_STATE_MISMATCH:
                return 'Underflow or the modes mismatch';
            case JSON_ERROR_CTRL_CHAR:
                return 'Unexpected control character found';
            case JSON_ERROR_SYNTAX:
                return 'Syntax error, malformed JSON';
            case JSON_ERROR_UTF8:
                return 'Malformed UTF-8 characters, possibly incorrectly encoded';
            default:
                return 'Unknown error';
        }
    }

    /**
     * {@inheritDoc}
     *
     * @param array<string, mixed>  $data
     * @param ManifestFileInterface $resource
     *
     * @return list<Asset>
     */
    protected function parseData(array $data, ManifestFileInterface $resource): array
    {
        $directory = trailingslashit(dirname($resource->getManifestPath()));
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
                ? $this->handleGenerator->generate($chunkName, $chunk)
                : pathinfo($chunkName, PATHINFO_FILENAME);
            $sanitizedFile = $this->sanitizeFileName($chunk['file']);
            $fileUrl = $this->directoryUrl . $sanitizedFile;
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

    /**
     * The "file"-value can contain:
     *  - URL
     *  - Path to current folder
     *  - Absolute path
     *
     * We try to build a clean path which will be appended to the directoryPath or urlPath.
     *
     * @param string $file
     *
     * @return string
     */
    protected function sanitizeFileName(string $file): string
    {
        // Check, if the given "file"-value is an URL
        $parsedUrl = parse_url($file);

        // the "file"-value can contain "./file.css" or "/file.css".

        return ltrim($parsedUrl['path'] ?? $file, './');
    }

    /**
     * Internal function to resolve a location for a given file name.
     *
     * @param string $fileName
     *
     * @return int
     *
     * @example foo-customizer.css  -> Asset::CUSTOMIZER
     * @example foo-block.css       -> Asset::BLOCK_EDITOR_ASSETS
     * @example foo-login.css       -> Asset::LOGIN
     * @example foo.css             -> Asset::FRONTEND
     * @example foo-backend.css     -> Asset::BACKEND
     */
    protected function resolveLocation(string $fileName): int
    {
        if (stristr($fileName, '-backend')) {
            return Asset::BACKEND;
        }

        if (stristr($fileName, '-block')) {
            return Asset::BLOCK_EDITOR_ASSETS;
        }

        if (stristr($fileName, '-login')) {
            return Asset::LOGIN;
        }

        if (stristr($fileName, '-customizer-preview')) {
            return Asset::CUSTOMIZER_PREVIEW;
        }

        if (stristr($fileName, '-customizer')) {
            return Asset::CUSTOMIZER;
        }

        return Asset::FRONTEND;
    }
}
