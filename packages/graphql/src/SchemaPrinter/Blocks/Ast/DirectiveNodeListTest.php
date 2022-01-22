<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Ast;

use Closure;
use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Directive;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Events\DirectiveUsed;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Events\Event;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\SchemaPrinter\TestSettings;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Ast\DirectiveNodeList
 */
class DirectiveNodeListTest extends TestCase {
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
        $actual = (string) (new DirectiveNodeList(new Dispatcher(), $settings, $level, $used, $directives, $reason));

        Parser::directives($actual);

        self::assertEquals($expected, $actual);
    }

    /**
     * @covers ::__toString
     */
    public function testToStringEvent(): void {
        $a          = Parser::directive('@a');
        $b          = Parser::directive('@b');
        $spy        = Mockery::spy(static fn (Event $event) => null);
        $settings   = (new TestSettings())->setPrintDirectives(true);
        $dispatcher = new Dispatcher();

        $dispatcher->attach(Closure::fromCallable($spy));

        self::assertNotEmpty(
            (string) (new DirectiveNodeList($dispatcher, $settings, 0, 0, [$a, $b])),
        );

        $spy
            ->shouldHaveBeenCalled()
            ->withArgs(static function (Event $event) use ($a): bool {
                return $event instanceof DirectiveUsed
                    && $event->name === $a->name->value;
            })
            ->once();
        $spy
            ->shouldHaveBeenCalled()
            ->withArgs(static function (Event $event) use ($b): bool {
                return $event instanceof DirectiveUsed
                    && $event->name === $b->name->value;
            })
            ->once();
        $spy
            ->shouldHaveBeenCalled()
            ->twice();
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, ?array<DirectiveNode>, ?string}>
     */
    public function dataProviderToString(): array {
        $settings = new TestSettings();

        return [
            'null'                                      => [
                '',
                $settings,
                0,
                0,
                null,
                null,
            ],
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
                null,
                Directive::DEFAULT_DEPRECATION_REASON,
            ],
            'deprecated (custom reason)'                => [
                <<<'STRING'
                @deprecated(reason: "reason")
                STRING,
                $settings,
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
                @b(
                    b: 1234567890
                )
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
                    @b(
                        b: 1234567890
                    )
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
                $settings->setDirectiveFilter(static function (DirectiveNode $directive): bool {
                    return $directive->name->value === 'a';
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
        ];
    }
    // </editor-fold>
}
