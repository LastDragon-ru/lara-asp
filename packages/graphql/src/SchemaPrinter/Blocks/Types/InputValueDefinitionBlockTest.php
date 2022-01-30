<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types;

use GraphQL\Language\Parser;
use GraphQL\Type\Definition\FieldArgument;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Misc\DirectiveResolver;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Misc\PrinterSettings;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\SchemaPrinter\TestSettings;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types\InputValueDefinitionBlock
 */
class InputValueDefinitionBlockTest extends TestCase {
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
        FieldArgument $definition,
    ): void {
        $settings = new PrinterSettings($this->app->make(DirectiveResolver::class), $settings);
        $actual   = (string) (new InputValueDefinitionBlock($settings, $level, $used, $definition));

        Parser::inputValueDefinition($actual);

        self::assertEquals($expected, $actual);
    }

    /**
     * @covers ::__toString
     */
    public function testStatistics(): void {
        $settings   = new PrinterSettings($this->app->make(DirectiveResolver::class), new TestSettings());
        $definition = new FieldArgument([
            'name'    => 'a',
            'type'    => new NonNull(
                new ObjectType([
                    'name' => 'A',
                ]),
            ),
            'astNode' => Parser::inputValueDefinition('test: Test! @a'),
        ]);

        $block = new InputValueDefinitionBlock($settings, 0, 0, $definition);

        self::assertNotEmpty((string) $block);
        self::assertEquals(['A' => 'A'], $block->getUsedTypes());
        self::assertEquals(['a' => 'a'], $block->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, FieldArgument}>
     */
    public function dataProviderToString(): array {
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
                $settings->setPrintDirectives(true),
                0,
                0,
                new FieldArgument([
                    'name'        => 'test',
                    'type'        => new NonNull(
                        new ObjectType([
                            'name' => 'Test',
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
                new FieldArgument([
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
                new FieldArgument([
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
                new FieldArgument([
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
