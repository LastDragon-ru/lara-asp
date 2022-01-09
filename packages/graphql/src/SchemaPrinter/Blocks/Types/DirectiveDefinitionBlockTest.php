<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types;

use Closure;
use GraphQL\Language\AST\DirectiveDefinitionNode;
use GraphQL\Language\DirectiveLocation;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\FieldArgument;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Events\Event;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Events\TypeUsed;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings\DefaultSettings;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types\DirectiveDefinitionBlock
 */
class DirectiveDefinitionBlockTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::__toString
     *
     * @dataProvider dataProviderToString
     */
    public function testToString(
        string $expected,
        Settings $settings,
        int $level,
        int $used,
        Directive $definition,
    ): void {
        $actual = (string) (new DirectiveDefinitionBlock(new Dispatcher(), $settings, $level, $used, $definition));
        $parsed = Parser::directiveDefinition($actual);

        self::assertEquals($expected, $actual);
        self::assertInstanceOf(DirectiveDefinitionNode::class, $parsed);
    }

    /**
     * @covers ::__toString
     */
    public function testToStringEvent(): void {
        $spy        = Mockery::spy(static fn (Event $event) => null);
        $settings   = new DefaultSettings();
        $dispatcher = new Dispatcher();
        $definition = new Directive([
            'name'      => 'A',
            'args'      => [
                'a' => [
                    'type' => new ObjectType(['name' => 'B']),
                ],
            ],
            'locations' => [
                DirectiveLocation::FIELD,
            ],
        ]);

        $dispatcher->attach(Closure::fromCallable($spy));

        self::assertNotNull(
            (string) (new DirectiveDefinitionBlock($dispatcher, $settings, 0, 0, $definition)),
        );

        $spy
            ->shouldHaveBeenCalled()
            ->withArgs(static function (Event $event): bool {
                return $event instanceof TypeUsed
                    && $event->name === 'B';
            })
            ->once();
        $spy
            ->shouldHaveBeenCalled()
            ->once();
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, FieldArgument}>
     */
    public function dataProviderToString(): array {
        return [
            'description'            => [
                <<<'STRING'
                """
                Description
                """
                directive @test on ARGUMENT_DEFINITION | ENUM
                STRING,
                new DefaultSettings(),
                0,
                0,
                new Directive([
                    'name'        => 'test',
                    'description' => 'Description',
                    'locations'   => [
                        DirectiveLocation::ARGUMENT_DEFINITION,
                        DirectiveLocation::ENUM,
                    ],
                ]),
            ],
            'repeatable'             => [
                <<<'STRING'
                directive @test repeatable on ARGUMENT_DEFINITION | ENUM
                STRING,
                new DefaultSettings(),
                0,
                0,
                new Directive([
                    'name'         => 'test',
                    'locations'    => [
                        DirectiveLocation::ARGUMENT_DEFINITION,
                        DirectiveLocation::ENUM,
                    ],
                    'isRepeatable' => true,
                ]),
            ],
            'args'                   => [
                <<<'STRING'
                directive @test(a: String) repeatable on ARGUMENT_DEFINITION | ENUM
                STRING,
                new DefaultSettings(),
                0,
                0,
                new Directive([
                    'name'         => 'test',
                    'args'         => [
                        'a' => [
                            'type' => Type::string(),
                        ],
                    ],
                    'locations'    => [
                        DirectiveLocation::ARGUMENT_DEFINITION,
                        DirectiveLocation::ENUM,
                    ],
                    'isRepeatable' => true,
                ]),
            ],
            'multiline + repeatable' => [
                <<<'STRING'
                directive @test(
                    a: String
                )
                repeatable on
                    | ARGUMENT_DEFINITION
                    | ENUM
                STRING,
                new class() extends DefaultSettings {
                    public function getIndent(): string {
                        return '    ';
                    }
                },
                0,
                120,
                new Directive([
                    'name'         => 'test',
                    'args'         => [
                        'a' => [
                            'type' => Type::string(),
                        ],
                    ],
                    'locations'    => [
                        DirectiveLocation::ARGUMENT_DEFINITION,
                        DirectiveLocation::ENUM,
                    ],
                    'isRepeatable' => true,
                ]),
            ],
            'multiline'              => [
                <<<'STRING'
                directive @test(
                    a: String
                )
                on
                    | ARGUMENT_DEFINITION
                    | ENUM
                STRING,
                new class() extends DefaultSettings {
                    public function getIndent(): string {
                        return '    ';
                    }
                },
                0,
                120,
                new Directive([
                    'name'      => 'test',
                    'args'      => [
                        'a' => [
                            'type' => Type::string(),
                        ],
                    ],
                    'locations' => [
                        DirectiveLocation::ARGUMENT_DEFINITION,
                        DirectiveLocation::ENUM,
                    ],
                ]),
            ],
            'multiline (no args)'    => [
                <<<'STRING'
                directive @test on
                    | ARGUMENT_DEFINITION
                    | ENUM
                STRING,
                new class() extends DefaultSettings {
                    public function getIndent(): string {
                        return '    ';
                    }
                },
                0,
                60,
                new Directive([
                    'name'      => 'test',
                    'locations' => [
                        DirectiveLocation::ARGUMENT_DEFINITION,
                        DirectiveLocation::ENUM,
                    ],
                ]),
            ],
            'indent'                 => [
                <<<'STRING'
                directive @test(
                        a: String
                    )
                    on
                        | ARGUMENT_DEFINITION
                        | ENUM
                STRING,
                new class() extends DefaultSettings {
                    public function getIndent(): string {
                        return '    ';
                    }
                },
                1,
                120,
                new Directive([
                    'name'      => 'test',
                    'args'      => [
                        'a' => [
                            'type' => Type::string(),
                        ],
                    ],
                    'locations' => [
                        DirectiveLocation::ARGUMENT_DEFINITION,
                        DirectiveLocation::ENUM,
                    ],
                ]),
            ],
            'normalized'             => [
                <<<'STRING'
                directive @test on ENUM | INPUT_FIELD_DEFINITION | OBJECT
                STRING,
                new class() extends DefaultSettings {
                    public function getIndent(): string {
                        return '    ';
                    }

                    public function isNormalizeDirectiveLocations(): bool {
                        return true;
                    }
                },
                0,
                0,
                new Directive([
                    'name'      => 'test',
                    'locations' => [
                        DirectiveLocation::OBJECT,
                        DirectiveLocation::ENUM,
                        DirectiveLocation::INPUT_FIELD_DEFINITION,
                    ],
                ]),
            ],
        ];
    }
    // </editor-fold>
}
