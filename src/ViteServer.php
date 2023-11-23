<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite;

use Kaiseki\WordPress\Environment\EnvironmentInterface;

use function curl_close;
use function Env\env;
use function is_bool;

use const CURLINFO_HTTP_CODE;
use const CURLOPT_RETURNTRANSFER;

final class ViteServer implements ViteServerInterface
{
    private const VITE_CLIENT = '@vite/client';

    private ?bool $isViteClientActive = null;

    public function __construct(
        private readonly EnvironmentInterface $environment,
        private readonly string $host = 'localhost',
        private readonly int $port = 5173,
    ) {
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getServerUrl(): string
    {
        return \Safe\sprintf(
            'http://%s:%s/',
            env('VITE_HOST') !== null ? env('VITE_HOST') : $this->host,
            env('VITE_PORT') !== null ? env('VITE_PORT') : $this->port,
        );
    }

    public function isHot(): bool
    {
        if (!$this->environment->isLocal() && !$this->environment->isDevelopment()) {
            return false;
        }
        if (is_bool($this->isViteClientActive)) {
            return $this->isViteClientActive;
        }
        $url = trailingslashit(self::getServerUrl()) . self::VITE_CLIENT;
        return $this->isViteClientActive = $this->checkUrlWithCurl($url);
    }

    private function checkUrlWithCurl(string $url): bool
    {
        $ch = \Safe\curl_init($url);
        \Safe\curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        \Safe\curl_exec($ch);
        $httpCode = \Safe\curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // @phpstan-ignore-next-line
        curl_close($ch);
        return $httpCode === 200;
    }
}
