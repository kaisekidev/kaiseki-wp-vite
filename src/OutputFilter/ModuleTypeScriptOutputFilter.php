<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\OutputFilter;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\OutputFilter\AssetOutputFilter;
use Inpsyde\Assets\Script;

use function preg_match;
use function preg_replace;

class ModuleTypeScriptOutputFilter implements AssetOutputFilter
{
    public function __invoke(string $html, Asset $asset): string
    {
        if (!$asset instanceof Script) {
            return $html;
        }
        if ((bool)preg_match('/type=["\'][^"\']*["\']/', $html)) {
            return preg_replace('/type=["\'][^"\']*["\']/', 'type="module"', $html) ?? $html;
        }

        return preg_replace('/<script/', '<script type="module"', $html) ?? $html;
    }
}
