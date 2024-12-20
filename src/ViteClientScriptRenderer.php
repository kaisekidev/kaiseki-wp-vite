<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite;

use Kaiseki\WordPress\Hook\HookProviderInterface;

use function add_action;
use function function_exists;
use function get_current_screen;
use function is_admin;
use function sprintf;
use function trailingslashit;

final class ViteClientScriptRenderer implements HookProviderInterface
{
    public function __construct(
        private readonly ViteServerInterface $viteServer,
    ) {
    }

    public function addHooks(): void
    {
        add_action('wp_head', [$this, 'renderViteClientScript'], 1);
        add_action('admin_head', [$this, 'renderViteClientScript'], 1);
        add_action('admin_head', [$this, 'renderViteClientScript'], 1);
    }

    public function renderViteClientScript(): void
    {
        if (!$this->viteServer->isHot() || (is_admin() && !$this->isBlockEditor())) {
            return;
        }

        echo sprintf(
            '<script type="module">
                import RefreshRuntime from "%1$s@react-refresh"
                RefreshRuntime.injectIntoGlobalHook(window)
                window.$RefreshReg$ = () => {}
                window.$RefreshSig$ = () => (type) => type
                window.__vite_plugin_react_preamble_installed__ = true
            </script>
            <script type="module" src="%1$s@vite/client"></script>',
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
