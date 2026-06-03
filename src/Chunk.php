<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite;

use function array_filter;
use function array_values;
use function is_array;
use function is_bool;
use function is_string;
use function pathinfo;

use const PATHINFO_FILENAME;

class Chunk implements ChunkInterface
{
    /** @var array<array-key, mixed> */
    private readonly array $data;

    /**
     * @param string                  $key
     * @param array<array-key, mixed> $data
     */
    public function __construct(
        private readonly string $key,
        array $data,
    ) {
        $this->data = $data;
    }

    /**
     * @return array<array-key, mixed>
     */
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
        return $this->stringList($this->data['css'] ?? []);
    }

    public function getFile(): string
    {
        $file = $this->data['file'] ?? '';

        return is_string($file) ? $file : '';
    }

    public function getImports(): array
    {
        return $this->stringList($this->data['imports'] ?? []);
    }

    public function getSourceFileName(): string
    {
        return pathinfo($this->getSource(), PATHINFO_FILENAME);
    }

    public function getSource(): string
    {
        $source = $this->data['src'] ?? '';

        return is_string($source) ? $source : '';
    }

    public function isEntry(): bool
    {
        $isEntry = $this->data['isEntry'] ?? false;

        return is_bool($isEntry) ? $isEntry : false;
    }

    /**
     * @param mixed $value
     *
     * @return list<string>
     */
    private function stringList(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_values(array_filter($value, is_string(...)));
    }
}
