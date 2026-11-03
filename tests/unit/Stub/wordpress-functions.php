<?php

declare(strict_types=1);

use Kaiseki\Test\Unit\WordPress\Vite\Stub\WordPressContext;

// The unit tests run without WordPress. Stub the conditionals the code under test calls.
if (!function_exists('is_admin')) {
    function is_admin(): bool
    {
        return WordPressContext::$isAdmin;
    }
}
