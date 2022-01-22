<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types;

use Closure;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Events\Event;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Events\TypeUsed;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\SchemaPrinter\TestSettings;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Mockery;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types\InterfaceTypeDefinitionBlock
 */
class InterfaceTypeDefinitionBlockTest extends TestCase {
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
        InterfaceType $definition,
    ): void {
        $actual = (string) (new InterfaceTypeDefinitionBlock(new Dispatcher(), $settings, $level, $used, $definition));

        Parser::interfaceTypeDefinition($actual);

        self::assertEquals($expected, $actual);
    }

    /**
     * @covers ::__toString
     */
    public function testToStringEvent(): void {
        $spy        = Mockery::spy(static fn (Event $event) => null);
        $settings   = new TestSettings();
        $dispatcher = new Dispatcher();
        $definition = new InterfaceType([
            'name'   => 'A',
            'fields' => [
                'b' => [
                    'name' => 'b',
                    'type' => new ObjectType([
                        'name' => 'B',
                    ]),
                    'args' => [
                        'c' => [
                            'type' => new ObjectType([
                                'name' => 'C',
                            ]),
                        ],
                    ],
                ],
            ],
        ]);

        $dispatcher->attach(Closure::fromCallable($spy));

        self::assertNotEmpty(
            (string) (new InterfaceTypeDefinitionBlock($dispatcher, $settings, 0, 0, $definition)),
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
            ->withArgs(static function (Event $event): bool {
                return $event instanceof TypeUsed
                    && $event->name === 'C';
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
     * @return array<string,array{string, Settings, int, int, InterfaceType}>
     */
    public function dataProviderToString(): array {
        $settings = (new TestSettings())
            ->setNormalizeFields(false)
            ->setNormalizeInterfaces(false)
            ->setAlwaysMultilineInterfaces(false);

        return [
            'description + directives'                    => [
                <<<'STRING'
                """
                Description
                """
                interface Test
                @a
                STRING,
                $settings->setPrintDirectives(true),
                0,
                0,
                new InterfaceType([
                    'name'        => 'Test',
                    'astNode'     => Parser::interfaceTypeDefinition('interface Test @a'),
                    'description' => 'Description',
                ]),
            ],
            'description + directives + fields'           => [
                <<<'STRING'
                """
                Description
                """
                interface Test
                @a
                {
                    c: C

                    """
                    Description
                    """
                    b(b: Int): B

                    a(a: Int): A
                }
                STRING,
                $settings->setPrintDirectives(true),
                0,
                0,
                new InterfaceType([
                    'name'        => 'Test',
                    'astNode'     => Parser::interfaceTypeDefinition('interface Test @a'),
                    'description' => 'Description',
                    'fields'      => [
                        [
                            'name' => 'c',
                            'type' => new ObjectType([
                                'name' => 'C',
                            ]),
                        ],
                        [
                            'name'        => 'b',
                            'type'        => new ObjectType([
                                'name' => 'B',
                            ]),
                            'args'        => [
                                'b' => [
                                    'type' => Type::int(),
                                ],
                            ],
                            'description' => 'Description',
                        ],
                        [
                            'name' => 'a',
                            'type' => new ObjectType([
                                'name' => 'A',
                            ]),
                            'args' => [
                                'a' => [
                                    'type' => Type::int(),
                                ],
                            ],
                        ],
                    ],
                ]),
            ],
            'fields'                                      => [
                <<<'STRING'
                interface Test {
                    a: String
                }
                STRING,
                $settings,
                0,
                0,
                new InterfaceType([
                    'name'   => 'Test',
                    'fields' => [
                        [
                            'name' => 'a',
                            'type' => Type::string(),
                        ],
                    ],
                ]),
            ],
            'implements + directives + fields'            => [
                <<<'STRING'
                interface Test implements B & A
                @a
                {
                    a: String
                }
                STRING,
                $settings->setPrintDirectives(true),
                0,
                0,
                new InterfaceType([
                    'name'       => 'Test',
                    'astNode'    => Parser::interfaceTypeDefinition('interface Test @a'),
                    'fields'     => [
                        [
                            'name' => 'a',
                            'type' => Type::string(),
                        ],
                    ],
                    'interfaces' => [
                        new InterfaceType(['name' => 'B']),
                        new InterfaceType(['name' => 'A']),
                    ],
                ]),
            ],
            'implements(multiline) + directives + fields' => [
                <<<'STRING'
                interface Test implements
                    & B
                    & A
                @a
                {
                    a: String
                }
                STRING,
                $settings->setPrintDirectives(true),
                0,
                120,
                new InterfaceType([
                    'name'       => 'Test',
                    'astNode'    => Parser::interfaceTypeDefinition('interface Test @a'),
                    'fields'     => [
                        [
                            'name' => 'a',
                            'type' => Type::string(),
                        ],
                    ],
                    'interfaces' => [
                        new InterfaceType(['name' => 'B']),
                        new InterfaceType(['name' => 'A']),
                    ],
                ]),
            ],
            'implements(multiline) + fields'              => [
                <<<'STRING'
                interface Test implements
                    & B
                    & A
                {
                    a: String
                }
                STRING,
                $settings,
                0,
                120,
                new InterfaceType([
                    'name'       => 'Test',
                    'fields'     => [
                        [
                            'name' => 'a',
                            'type' => Type::string(),
                        ],
                    ],
                    'interfaces' => [
                        new InterfaceType(['name' => 'B']),
                        new InterfaceType(['name' => 'A']),
                    ],
                ]),
            ],
            'implements + fields'                         => [
                <<<'STRING'
                interface Test implements B & A {
                    a: String
                }
                STRING,
                $settings,
                0,
                0,
                new InterfaceType([
                    'name'       => 'Test',
                    'fields'     => [
                        [
                            'name' => 'a',
                            'type' => Type::string(),
                        ],
                    ],
                    'interfaces' => [
                        new InterfaceType(['name' => 'B']),
                        new InterfaceType(['name' => 'A']),
                    ],
                ]),
            ],
            'implements(normalized) + fields'             => [
                <<<'STRING'
                interface Test implements
                    & A
                    & B
                {
                    a: String
                }
                STRING,
                $settings->setNormalizeInterfaces(true),
                0,
                120,
                new InterfaceType([
                    'name'       => 'Test',
                    'fields'     => [
                        [
                            'name' => 'a',
                            'type' => Type::string(),
                        ],
                    ],
                    'interfaces' => [
                        new InterfaceType(['name' => 'B']),
                        new InterfaceType(['name' => 'A']),
                    ],
                ]),
            ],
            'indent'                                      => [
                <<<'STRING'
                interface Test implements
                        & A
                        & B
                    {
                        a: String
                    }
                STRING,
                $settings->setNormalizeInterfaces(true),
                1,
                120,
                new InterfaceType([
                    'name'       => 'Test',
                    'fields'     => [
                        [
                            'name' => 'a',
                            'type' => Type::string(),
                        ],
                    ],
                    'interfaces' => [
                        new InterfaceType(['name' => 'B']),
                        new InterfaceType(['name' => 'A']),
                    ],
                ]),
            ],
            'implements always multiline'                 => [
                <<<'STRING'
                interface Test implements
                    & A
                {
                    a: String
                }
                STRING,
                $settings
                    ->setAlwaysMultilineInterfaces(true),
                0,
                0,
                new InterfaceType([
                    'name'       => 'Test',
                    'fields'     => [
                        [
                            'name' => 'a',
                            'type' => Type::string(),
                        ],
                    ],
                    'interfaces' => [
                        new InterfaceType(['name' => 'A']),
                    ],
                ]),
            ],
        ];
    }
    // </editor-fold>
}
