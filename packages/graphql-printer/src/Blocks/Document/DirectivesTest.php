<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Directive as GraphQLDirective;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\TestSettings;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Directives::class)]
class DirectivesTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderSerialize
     *
     * @param list<DirectiveNode> $directives
     */
    public function testSerialize(
        string $expected,
        Settings $settings,
        int $level,
        int $used,
        array $directives,
        string $reason = null,
    ): void {
        $collector = new Collector();
        $context   = new Context($settings, null, null);
        $actual    = (new Directives($context, $directives, $reason))->serialize($collector, $level, $used);

        Parser::directives($actual);

        self::assertEquals($expected, $actual);
    }

    public function testStatistics(): void {
        $a         = Parser::directive('@a');
        $b         = Parser::directive('@b');
        $settings  = (new TestSettings())->setPrintDirectives(true);
        $context   = new Context($settings, null, null);
        $collector = new Collector();
        $block     = new Directives($context, [$a, $b]);
        $content   = $block->serialize($collector, 0, 0);

        self::assertNotEmpty($content);
        self::assertEquals([], $collector->getUsedTypes());
        self::assertEquals(['@a' => '@a', '@b' => '@b'], $collector->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, list<DirectiveNode>, ?string}>
     */
    public static function dataProviderSerialize(): array {
        $settings = (new TestSettings())
            ->setNormalizeDirectives(false)
            ->setAlwaysMultilineDirectives(true)
            ->setAlwaysMultilineArguments(false);

        return [
            'empty'                                     => [
                '',
                $settings,
                0,
                0,
                [],
                null,
            ],
            'directives'                                => [
                <<<'STRING'
                @b(b: 123)
                @a
                STRING,
                $settings,
                0,
                0,
                [
                    Parser::directive('@b(b: 123)'),
                    Parser::directive('@a'),
                ],
                null,
            ],
            'deprecated (default reason)'               => [
                <<<'STRING'
                @deprecated
                STRING,
                $settings,
                0,
                0,
                [],
                GraphQLDirective::DEFAULT_DEPRECATION_REASON,
            ],
            'deprecated (custom reason)'                => [
                <<<'STRING'
                @deprecated(reason: "reason")
                STRING,
                $settings,
                0,
                0,
                [],
                'reason',
            ],
            'directives and deprecated (custom reason)' => [
                <<<'STRING'
                @b(b: 123)
                @deprecated(reason: "reason")
                STRING,
                $settings,
                0,
                0,
                [
                    Parser::directive('@b(b: 123)'),
                    Parser::directive('@deprecated(reason: "should be ignored")'),
                ],
                'reason',
            ],
            'line length'                               => [
                <<<'STRING'
                @deprecated(
                    reason: "very very very long reason"
                )
                @a(a: 123)
                @b(b: 1234567890)
                STRING,
                $settings,
                0,
                70,
                [
                    Parser::directive('@a(a: 123)'),
                    Parser::directive('@b(b: 1234567890)'),
                ],
                'very very very long reason',
            ],
            'indent'                                    => [
                <<<'STRING'
                @deprecated(
                        reason: "very very very long reason"
                    )
                    @a(a: 123)
                    @b(b: 1234567890)
                STRING,
                $settings,
                1,
                70,
                [
                    Parser::directive('@a(a: 123)'),
                    Parser::directive('@b(b: 1234567890)'),
                ],
                'very very very long reason',
            ],
            'filter'                                    => [
                <<<'STRING'
                @a(a: 123)
                STRING,
                $settings->setDirectiveFilter(static function (string $directive): bool {
                    return $directive === 'a';
                }),
                0,
                0,
                [
                    Parser::directive('@a(a: 123)'),
                    Parser::directive('@b(b: 1234567890)'),
                    Parser::directive('@c'),
                ],
                null,
            ],
            'args always multiline'                     => [
                <<<'STRING'
                @a(
                    a: 123
                )
                STRING,
                $settings
                    ->setAlwaysMultilineArguments(true),
                0,
                0,
                [
                    Parser::directive('@a(a: 123)'),
                ],
                null,
            ],
            'one line'                                  => [
                <<<'STRING'
                @b(b: 123) @a
                STRING,
                $settings
                    ->setAlwaysMultilineDirectives(false),
                0,
                0,
                [
                    Parser::directive('@b(b: 123)'),
                    Parser::directive('@a'),
                ],
                null,
            ],
            'one line too long'                         => [
                <<<'STRING'
                @b(
                    b: 123
                )
                @a
                STRING,
                $settings
                    ->setLineLength(5)
                    ->setAlwaysMultilineDirectives(false),
                0,
                0,
                [
                    Parser::directive('@b(b: 123)'),
                    Parser::directive('@a'),
                ],
                null,
            ],
            'normalized'                                => [
                <<<'STRING'
                @a
                @b(b: 123)
                STRING,
                $settings
                    ->setNormalizeDirectives(true),
                0,
                0,
                [
                    Parser::directive('@b(b: 123)'),
                    Parser::directive('@a'),
                ],
                null,
            ],
        ];
    }
    // </editor-fold>
}
