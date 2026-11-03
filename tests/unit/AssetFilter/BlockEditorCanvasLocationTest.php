<?php

declare(strict_types=1);

namespace Kaiseki\Test\Unit\WordPress\Vite\AssetFilter;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;
use Kaiseki\Test\Unit\WordPress\Vite\Stub\WordPressContext;
use Kaiseki\WordPress\Vite\AssetFilter\ScriptFilter;
use Kaiseki\WordPress\Vite\AssetFilter\StyleFilter;
use Kaiseki\WordPress\Vite\ChunkInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../Stub/wordpress-functions.php';

final class BlockEditorCanvasLocationTest extends TestCase
{
    protected function tearDown(): void
    {
        WordPressContext::reset();
    }

    public function testStyleUsesTheBlockAssetsLocation(): void
    {
        $style = $this->style(static fn(StyleFilter $filter) => $filter->forBlockEditorCanvasLocation());

        self::assertSame(Asset::BLOCK_ASSETS, $style->location());
    }

    public function testScriptUsesTheBlockAssetsLocation(): void
    {
        $filter = ScriptFilter::create();
        $filter->forBlockEditorCanvasLocation();
        $script = $filter(new Script('handle', 'https://example.org/script.js'), self::createStub(ChunkInterface::class));

        self::assertInstanceOf(Script::class, $script);
        self::assertSame(Asset::BLOCK_ASSETS, $script->location());
    }

    public function testEnqueuesInTheAdmin(): void
    {
        $style = $this->style(static fn(StyleFilter $filter) => $filter->forBlockEditorCanvasLocation());

        WordPressContext::$isAdmin = true;

        self::assertTrue($style->enqueue());
    }

    public function testDoesNotEnqueueOnTheFrontend(): void
    {
        $style = $this->style(static fn(StyleFilter $filter) => $filter->forBlockEditorCanvasLocation());

        WordPressContext::$isAdmin = false;

        self::assertFalse($style->enqueue());
    }

    /**
     * @return iterable<string, array{bool|callable(): bool, bool, bool}>
     */
    public static function enqueueCombinations(): iterable
    {
        yield 'admin, enqueue true' => [true, true, true];
        yield 'admin, enqueue false' => [false, true, false];
        yield 'admin, callable true' => [static fn(): bool => true, true, true];
        yield 'admin, callable false' => [static fn(): bool => false, true, false];
        yield 'frontend, enqueue true' => [true, false, false];
        yield 'frontend, callable true' => [static fn(): bool => true, false, false];
    }

    /**
     * @param bool|callable(): bool $enqueue
     */
    #[DataProvider('enqueueCombinations')]
    public function testCombinesWithCanEnqueue(bool|callable $enqueue, bool $isAdmin, bool $expected): void
    {
        $style = $this->style(static fn(StyleFilter $filter) => $filter->forBlockEditorCanvasLocation()->canEnqueue($enqueue));

        WordPressContext::$isAdmin = $isAdmin;

        self::assertSame($expected, $style->enqueue());
    }

    public function testCombinesWithCanEnqueueSetBefore(): void
    {
        $style = $this->style(static fn(StyleFilter $filter) => $filter->canEnqueue(static fn(): bool => false)->forBlockEditorCanvasLocation());

        WordPressContext::$isAdmin = true;

        self::assertFalse($style->enqueue());
    }

    public function testAnotherLocationClearsTheAdminCondition(): void
    {
        $style = $this->style(static fn(StyleFilter $filter) => $filter->forBlockEditorCanvasLocation()->forFrontendLocation());

        WordPressContext::$isAdmin = false;

        self::assertSame(Asset::FRONTEND, $style->location());
        self::assertTrue($style->enqueue());
    }

    /**
     * @param callable(StyleFilter): mixed $configure
     */
    private function style(callable $configure): Style
    {
        $filter = StyleFilter::create();
        $configure($filter);
        $style = $filter(new Style('handle', 'https://example.org/style.css'), self::createStub(ChunkInterface::class));
        self::assertInstanceOf(Style::class, $style);

        return $style;
    }
}
