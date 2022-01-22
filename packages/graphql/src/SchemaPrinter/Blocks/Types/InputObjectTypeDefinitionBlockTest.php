<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types;

use Closure;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\InputObjectType;
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
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types\InputObjectTypeDefinitionBlock
 */
class InputObjectTypeDefinitionBlockTest extends TestCase {
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
        InputObjectType $definition,
    ): void {
        $actual = (string) (new InputObjectTypeDefinitionBlock(
            new Dispatcher(),
            $settings,
            $level,
            $used,
            $definition,
        ));

        Parser::inputObjectTypeDefinition($actual);

        self::assertEquals($expected, $actual);
    }

    /**
     * @covers ::__toString
     */
    public function testToStringEvent(): void {
        $spy        = Mockery::spy(static fn (Event $event) => null);
        $settings   = new TestSettings();
        $dispatcher = new Dispatcher();
        $definition = new InputObjectType([
            'name'   => 'A',
            'fields' => [
                'b' => [
                    'name' => 'b',
                    'type' => new InputObjectType([
                        'name' => 'B',
                    ]),
                ],
            ],
        ]);

        $dispatcher->attach(Closure::fromCallable($spy));

        self::assertNotEmpty(
            (string) (new InputObjectTypeDefinitionBlock($dispatcher, $settings, 0, 0, $definition)),
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
     * @return array<string,array{string, Settings, int, int, InputObjectType}>
     */
    public function dataProviderToString(): array {
        $settings = (new TestSettings())
            ->setNormalizeFields(false);

        return [
            'description + directives'          => [
                <<<'STRING'
                """
                Description
                """
                input Test
                @a
                STRING,
                $settings->setPrintDirectives(true),
                0,
                0,
                new InputObjectType([
                    'name'        => 'Test',
                    'astNode'     => Parser::inputObjectTypeDefinition('input Test @a'),
                    'description' => 'Description',
                ]),
            ],
            'description + directives + fields' => [
                <<<'STRING'
                """
                Description
                """
                input Test
                @a
                {
                    c: C

                    """
                    Description
                    """
                    b: B

                    a: A
                }
                STRING,
                $settings->setPrintDirectives(true),
                0,
                0,
                new InputObjectType([
                    'name'        => 'Test',
                    'astNode'     => Parser::inputObjectTypeDefinition('input Test @a'),
                    'description' => 'Description',
                    'fields'      => [
                        [
                            'name' => 'c',
                            'type' => new InputObjectType([
                                'name' => 'C',
                            ]),
                        ],
                        [
                            'name'        => 'b',
                            'type'        => new InputObjectType([
                                'name' => 'B',
                            ]),
                            'description' => 'Description',
                        ],
                        [
                            'name' => 'a',
                            'type' => new InputObjectType([
                                'name' => 'A',
                            ]),
                        ],
                    ],
                ]),
            ],
            'fields'                            => [
                <<<'STRING'
                input Test {
                    a: String
                }
                STRING,
                $settings,
                0,
                0,
                new InputObjectType([
                    'name'   => 'Test',
                    'fields' => [
                        [
                            'name' => 'a',
                            'type' => Type::string(),
                        ],
                    ],
                ]),
            ],
            'indent'                            => [
                <<<'STRING'
                input Test {
                        a: String
                    }
                STRING,
                $settings->setNormalizeInterfaces(true),
                1,
                120,
                new InputObjectType([
                    'name'   => 'Test',
                    'fields' => [
                        [
                            'name' => 'a',
                            'type' => Type::string(),
                        ],
                    ],
                ]),
            ],
        ];
    }
    // </editor-fold>
}
