<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite;

/**
 * Implementation of Vite manifest.json parsing into Assets.
 *
 * @link https://vitejs.dev/guide/backend-integration.html
 *
 * @phpstan-type ChunkData array{
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
interface ChunkInterface
{
    /**
     * @return ChunkData
     */
    public function getChunkData(): array;

    public function getChunkKey(): string;

    /**
     * @return list<string>
     */
    public function getCss(): array;

    public function getFile(): string;

    public function getSource(): string;

    public function getSourceFileName(): string;

    public function isEntry(): bool;
}
