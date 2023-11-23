<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite;

interface ViteServerInterface
{
    public function getHost(): string;

    public function getPort(): int;

    public function getServerUrl(): string;

    public function isHot(): bool;
}
