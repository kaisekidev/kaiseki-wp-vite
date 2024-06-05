<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite;

/**
 * @phpstan-import-type ChunkData from ChunkInterface
 */
class Chunk implements ChunkInterface
{
    /**
     * @param string    $key
     * @param ChunkData $data
     */
    public function __construct(
        private readonly string $key,
        private readonly array $data,
    ) {
    }

    public function getChunkData(): array
    {
        return $this->data;
    }

    public function getChunkKey(): string
    {
        return $this->key;
    }

    public function getCss(): array
    {
        return $this->data['css'] ?? [];
    }

    public function getFile(): string
    {
        return $this->data['file'] ?? '';
    }

    public function getSourceFileName(): string
    {
        return pathinfo($this->getSource(), PATHINFO_FILENAME);
    }

    public function getSource(): string
    {
        return $this->data['src'] ?? '';
    }

    public function isEntry(): bool
    {
        return $this->data['isEntry'] ?? false;
    }
}
