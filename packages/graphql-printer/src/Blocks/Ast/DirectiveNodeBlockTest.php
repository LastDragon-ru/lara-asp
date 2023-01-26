<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Ast;

use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\Parser;
use GraphQL\Language\Printer;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Misc\DirectiveResolver;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\PrinterSettings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Ast\DirectiveNodeBlock
 */
class DirectiveNodeBlockTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderToString
     */
    public function testToString(
        string $expected,
        Settings $settings,
        int $level,
        int $used,
        DirectiveNode $node,
    ): void {
        $settings = new PrinterSettings($this->app->make(DirectiveResolver::class), $settings);
        $actual   = (string) (new DirectiveNodeBlock($settings, $level, $used, $node));
        $parsed   = Parser::directive($actual);

        self::assertEquals($expected, $actual);

        if (!$settings->isNormalizeArguments()) {
            self::assertEquals(
                Printer::doPrint($node),
                Printer::doPrint($parsed),
            );
        }
    }

    public function testStatistics(): void {
        $settings = new TestSettings();
        $settings = new PrinterSettings($this->app->make(DirectiveResolver::class), $settings);
        $node     = Parser::directive('@test');
        $block    = new DirectiveNodeBlock($settings, 0, 0, $node);

        self::assertNotEmpty((string) $block);
        self::assertEquals([], $block->getUsedTypes());
        self::assertEquals(['@test' => '@test'], $block->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, DirectiveNode}>
     */
    public function dataProviderToString(): array {
        $settings = (new TestSettings())
            ->setNormalizeArguments(false)
            ->setAlwaysMultilineArguments(false);

        return [
            'without arguments'                 => [
                '@directive',
                $settings,
                0,
                0,
                Parser::directive('@directive'),
            ],
            'without arguments (level)'         => [
                '@directive',
                $settings,
                0,
                0,
                Parser::directive('@directive'),
            ],
            'with arguments (short)'            => [
                '@directive(a: "a", b: "b")',
                $settings,
                0,
                0,
                Parser::directive('@directive(a: "a", b: "b")'),
            ],
            'with arguments (long)'             => [
                <<<'STRING'
                @directive(
                    b: "b"
                    a: "a"
                )
                STRING,
                $settings,
                0,
                120,
                Parser::directive('@directive(b: "b", a: "a")'),
            ],
            'with arguments (normalized)'       => [
                '@directive(a: "a", b: "b")',
                $settings->setNormalizeArguments(true),
                0,
                0,
                Parser::directive('@directive(b: "b", a: "a")'),
            ],
            'with arguments (indent)'           => [
                <<<'STRING'
                @directive(
                        b: "b"
                        a: "a"
                    )
                STRING,
                $settings,
                1,
                120,
                Parser::directive('@directive(b: "b", a: "a")'),
            ],
            'with arguments (always multiline)' => [
                <<<'STRING'
                @directive(
                    a: "a"
                )
                STRING,
                $settings
                    ->setAlwaysMultilineArguments(true),
                0,
                0,
                Parser::directive('@directive(a: "a")'),
            ],
        ];
    }
    // </editor-fold>
}
