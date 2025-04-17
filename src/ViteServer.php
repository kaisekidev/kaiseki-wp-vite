<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Kaiseki\WordPress\Environment\EnvironmentInterface;
use Throwable;

use function is_bool;
use function sprintf;
use function str_starts_with;
use function trailingslashit;

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
        return sprintf(
            '%s%s:%s/',
            str_starts_with($this->host, 'http') ? '' : 'http://',
            $this->host,
            $this->port,
        );
    }

    public function isHot(): bool
    {
        if (!$this->environment->isDevelopment() && !$this->environment->isLocal()) {
            return false;
        }

        if (is_bool($this->isViteClientActive)) {
            return $this->isViteClientActive;
        }

        $client = new Client();

        try {
            return $this->isViteClientActive = $client
                    ->get(
                        trailingslashit(self::getServerUrl()) . self::VITE_CLIENT,
                        [RequestOptions::HTTP_ERRORS => false]
                    )
                    ->getStatusCode() === 200;
        } catch (Throwable $e) {
        }

        return false;
    }
}
