<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Schema;

use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Argument;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Schema\InputValueDefinition
 */
class InputValueDefinitionTest extends TestCase {
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
        Argument $definition,
    ): void {
        $context = new Context($settings, null, null);
        $actual  = (string) (new InputValueDefinition($context, $level, $used, $definition));

        Parser::inputValueDefinition($actual);

        self::assertEquals($expected, $actual);
    }

    public function testStatistics(): void {
        $context    = new Context(new TestSettings(), null, null);
        $definition = new Argument([
            'name'    => 'a',
            'type'    => new NonNull(
                new ObjectType([
                    'name'   => 'A',
                    'fields' => [
                        'field' => [
                            'type' => Type::string(),
                        ],
                    ],
                ]),
            ),
            'astNode' => Parser::inputValueDefinition('test: Test! @a'),
        ]);

        $block = new InputValueDefinition($context, 0, 0, $definition);

        self::assertNotEmpty((string) $block);
        self::assertEquals(['A' => 'A'], $block->getUsedTypes());
        self::assertEquals(['@a' => '@a'], $block->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, Argument}>
     */
    public static function dataProviderToString(): array {
        $settings = new TestSettings();

        return [
            'without value'      => [
                <<<'STRING'
                """
                Description
                """
                test: Test!
                @a
                STRING,
                $settings
                    ->setPrintDirectives(true),
                0,
                0,
                new Argument([
                    'name'        => 'test',
                    'type'        => new NonNull(
                        new ObjectType([
                            'name'   => 'Test',
                            'fields' => [
                                'field' => [
                                    'type' => Type::string(),
                                ],
                            ],
                        ]),
                    ),
                    'astNode'     => Parser::inputValueDefinition('test: Test! @a'),
                    'description' => 'Description',
                ]),
            ],
            'with value (short)' => [
                <<<'STRING'
                """
                Description
                """
                test: [String!] = ["aaaaaaaaaaaaaaaaaaaaaaaaaa"]
                STRING,
                $settings,
                0,
                0,
                new Argument([
                    'name'         => 'test',
                    'type'         => new ListOfType(new NonNull(Type::string())),
                    'defaultValue' => [
                        'aaaaaaaaaaaaaaaaaaaaaaaaaa',
                    ],
                    'description'  => 'Description',
                ]),
            ],
            'with value (long)'  => [
                <<<'STRING'
                """
                Description
                """
                test: [String!] = [
                    "aaaaaaaaaaaaaaaaaaaaaaaaaa"
                ]
                STRING,
                $settings,
                0,
                120,
                new Argument([
                    'name'         => 'test',
                    'type'         => new ListOfType(new NonNull(Type::string())),
                    'defaultValue' => [
                        'aaaaaaaaaaaaaaaaaaaaaaaaaa',
                    ],
                    'description'  => 'Description',
                ]),
            ],
            'indent'             => [
                <<<'STRING'
                """
                    Description
                    """
                    test: [String!] = [
                        "aaaaaaaaaaaaaaaaaaaaaaaaaa"
                    ]
                STRING,
                $settings,
                1,
                70,
                new Argument([
                    'name'         => 'test',
                    'type'         => new ListOfType(new NonNull(Type::string())),
                    'defaultValue' => [
                        'aaaaaaaaaaaaaaaaaaaaaaaaaa',
                    ],
                    'description'  => 'Description',
                ]),
            ],
        ];
    }
    // </editor-fold>
}
