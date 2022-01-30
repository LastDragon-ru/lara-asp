<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks;

use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Misc\DirectiveResolver;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Misc\PrinterSettings;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\SchemaPrinter\TestSettings;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Mockery;

use function mb_strlen;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Block
 */
class BlockTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::getContent
     */
    public function testGetContent(): void {
        $settings = new TestSettings();
        $settings = new PrinterSettings($this->app->make(DirectiveResolver::class), $settings);
        $content  = 'content';
        $block    = Mockery::mock(BlockTest__Block::class, [$settings]);
        $block->shouldAllowMockingProtectedMethods();
        $block->makePartial();
        $block
            ->shouldReceive('content')
            ->once()
            ->andReturn($content);

        self::assertEquals($content, $block->getContent());
        self::assertEquals($content, $block->getContent());
    }

    /**
     * @covers ::getLength
     */
    public function testGetLength(): void {
        $settings = new TestSettings();
        $settings = new PrinterSettings($this->app->make(DirectiveResolver::class), $settings);
        $content  = 'content';
        $length   = mb_strlen($content);
        $block    = Mockery::mock(BlockTest__Block::class, [$settings]);
        $block->shouldAllowMockingProtectedMethods();
        $block->makePartial();
        $block
            ->shouldReceive('content')
            ->once()
            ->andReturn($content);

        self::assertEquals($length, $block->getLength());
        self::assertEquals($length, $block->getLength());
    }

    /**
     * @covers ::isMultiline
     *
     * @dataProvider dataProviderIsMultiline
     */
    public function testIsMultiline(bool $expected, Settings $settings, string $content): void {
        $settings = new PrinterSettings($this->app->make(DirectiveResolver::class), $settings);
        $block    = Mockery::mock(BlockTest__Block::class, [$settings]);
        $block->shouldAllowMockingProtectedMethods();
        $block->makePartial();
        $block
            ->shouldReceive('content')
            ->once()
            ->andReturn($content);

        self::assertEquals($expected, $block->isMultiline());
        self::assertEquals($expected, $block->isMultiline());
    }

    /**
     * @covers ::isEmpty
     *
     * @dataProvider dataProviderIsEmpty
     */
    public function testIsEmpty(bool $expected, string $content): void {
        $settings = new TestSettings();
        $settings = new PrinterSettings($this->app->make(DirectiveResolver::class), $settings);
        $block    = Mockery::mock(BlockTest__Block::class, [$settings]);
        $block->shouldAllowMockingProtectedMethods();
        $block->makePartial();
        $block
            ->shouldReceive('content')
            ->once()
            ->andReturn($content);

        self::assertEquals($expected, $block->isEmpty());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{bool, Settings, string}>
     */
    public function dataProviderIsMultiline(): array {
        $settings = new TestSettings();

        return [
            'single short line' => [
                false,
                $settings,
                'short line',
            ],
            'single long line'  => [
                false,
                $settings->setLineLength(5),
                'long line',
            ],
            'multi line'        => [
                true,
                $settings,
                "multi\nline",
            ],
        ];
    }

    /**
     * @return array<string, array{bool, string}>
     */
    public function dataProviderIsEmpty(): array {
        return [
            'empty'     => [true, ''],
            'non empty' => [false, 'content'],
        ];
    }
    // </editor-fold>
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class BlockTest__Block extends Block {
    public function getContent(): string {
        return parent::getContent();
    }

    public function getLength(): int {
        return parent::getLength();
    }

    public function isMultiline(): bool {
        return parent::isMultiline();
    }

    protected function content(): string {
        return '';
    }
}
