<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite;

use Kaiseki\WordPress\Hook\HookCallbackProviderInterface;

use function function_exists;

final class ViteClientScriptRenderer implements HookCallbackProviderInterface
{
    public function __construct(
        private readonly ViteServerInterface $viteServer,
    ) {
    }

    public function registerHookCallbacks(): void
    {
        add_action('wp_head', [$this, 'renderViteClientScript']);
        add_action('admin_head', [$this, 'renderViteClientScript']);
    }

    public function renderViteClientScript(): void
    {
        if (!$this->viteServer->isHot() || (is_admin() && !$this->isBlockEditor())) {
            return;
        }

        echo \Safe\sprintf(
            '<script type="module" src="%s@vite/client"></script>',
            trailingslashit($this->viteServer->getServerUrl())
        );
    }

    private function isBlockEditor(): bool
    {
        if (!is_admin() || !function_exists('get_current_screen')) {
            return false;
        }

        return (bool)get_current_screen()?->is_block_editor();
    }
}
