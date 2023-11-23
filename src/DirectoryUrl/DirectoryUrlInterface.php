<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\DirectoryUrl;

use Kaiseki\WordPress\Vite\ViteServerInterface;

interface DirectoryUrlInterface
{
    public function __invoke(ViteServerInterface $viteClient): string;
}
