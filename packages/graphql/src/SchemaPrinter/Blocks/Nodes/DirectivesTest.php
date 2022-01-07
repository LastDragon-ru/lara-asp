<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Nodes;

use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\NodeList;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Directive;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings\DefaultSettings;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Nodes\Directives
 */
class DirectivesTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::__toString
     *
     * @dataProvider dataProviderToString
     *
     * @param array<DirectiveNode> $directives
     */
    public function testToString(
        string $expected,
        Settings $settings,
        int $level,
        int $used,
        array|null $directives,
        string $reason = null,
    ): void {
        $actual = (string) (new Directives(new Dispatcher(), $settings, $level, $used, $directives, $reason));
        $parsed = Parser::directives($actual);

        self::assertEquals($expected, $actual);
        self::assertInstanceOf(NodeList::class, $parsed);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, DirectiveNode}>
     */
    public function dataProviderToString(): array {
        return [
            'null'                                      => [
                '',
                new class() extends DefaultSettings {
                    public function isIncludeDirectives(): bool {
                        return true;
                    }
                },
                0,
                0,
                null,
                null,
            ],
            'empty'                                     => [
                '',
                new class() extends DefaultSettings {
                    public function isIncludeDirectives(): bool {
                        return true;
                    }
                },
                0,
                0,
                [],
                null,
            ],
            'disabled'                                  => [
                '',
                new class() extends DefaultSettings {
                    public function isIncludeDirectives(): bool {
                        return false;
                    }
                },
                0,
                0,
                [
                    Parser::directive('@a'),
                ],
                null,
            ],
            'directives'                                => [
                <<<'STRING'
                @b(b: 123)
                @a
                STRING,
                new class() extends DefaultSettings {
                    public function isIncludeDirectives(): bool {
                        return true;
                    }
                },
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
                new class() extends DefaultSettings {
                    public function isIncludeDirectives(): bool {
                        return true;
                    }
                },
                0,
                0,
                null,
                Directive::DEFAULT_DEPRECATION_REASON,
            ],
            'deprecated (custom reason)'                => [
                <<<'STRING'
                @deprecated(reason: "reason")
                STRING,
                new class() extends DefaultSettings {
                    public function isIncludeDirectives(): bool {
                        return true;
                    }
                },
                0,
                0,
                null,
                'reason',
            ],
            'directives and deprecated (custom reason)' => [
                <<<'STRING'
                @deprecated(reason: "reason")
                @b(b: 123)
                STRING,
                new class() extends DefaultSettings {
                    public function isIncludeDirectives(): bool {
                        return true;
                    }
                },
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
                @b(
                    b: 1234567890
                )
                STRING,
                new class() extends DefaultSettings {
                    public function isIncludeDirectives(): bool {
                        return true;
                    }
                },
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
                    @b(
                        b: 1234567890
                    )
                STRING,
                new class() extends DefaultSettings {
                    public function isIncludeDirectives(): bool {
                        return true;
                    }
                },
                1,
                70,
                [
                    Parser::directive('@a(a: 123)'),
                    Parser::directive('@b(b: 1234567890)'),
                ],
                'very very very long reason',
            ],
        ];
    }
    // </editor-fold>
}
