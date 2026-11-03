<?php

declare(strict_types=1);

namespace Kaiseki\Test\Unit\WordPress\Vite\Stub;

/**
 * Controls the WordPress conditionals that the unit tests stub, see wordpress-functions.php.
 */
final class WordPressContext
{
    public static bool $isAdmin = false;

    public static function reset(): void
    {
        self::$isAdmin = false;
    }
}
