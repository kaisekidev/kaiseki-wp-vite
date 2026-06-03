<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite;

/**
 * Implementation of Vite manifest.json parsing into Assets.
 *
 * @link https://vitejs.dev/guide/backend-integration.html
 */
interface ChunkInterface
{
    /**
     * @return array<array-key, mixed>
     */
    public function getChunkData(): array;

    public function getChunkKey(): string;

    /**
     * @return list<string>
     */
    public function getCss(): array;

    public function getFile(): string;

    /**
     * @return list<string>
     */
    public function getImports(): array;

    public function getSource(): string;

    public function getSourceFileName(): string;

    public function isEntry(): bool;
}
