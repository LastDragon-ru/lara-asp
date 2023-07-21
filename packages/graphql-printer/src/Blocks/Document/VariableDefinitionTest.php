<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\VariableDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\TestSettings;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(VariableDefinition::class)]
class VariableDefinitionTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderSerialize
     */
    public function testSerialize(
        string $expected,
        Settings $settings,
        int $level,
        int $used,
        VariableDefinitionNode $definition,
        ?Schema $schema,
    ): void {
        $collector = new Collector();
        $context   = new Context($settings, null, $schema);
        $actual    = (new VariableDefinition($context, $definition))->serialize($collector, $level, $used);

        if ($expected) {
            Parser::variableDefinition($actual);
        }

        self::assertEquals($expected, $actual);
    }

    public function testStatistics(): void {
        $context    = new Context(new TestSettings(), null, null);
        $collector  = new Collector();
        $definition = Parser::variableDefinition(
            '$a: A = 123 @a @b',
        );
        $block      = new VariableDefinition($context, $definition);
        $content    = $block->serialize($collector, 0, 0);

        self::assertNotEmpty($content);
        self::assertEquals(['A' => 'A'], $collector->getUsedTypes());
        self::assertEquals(['@a' => '@a', '@b' => '@b'], $collector->getUsedDirectives());

        $astCollector = new Collector();
        $astBlock     = new VariableDefinition($context, Parser::variableDefinition($content));

        self::assertEquals($content, $astBlock->serialize($astCollector, 0, 0));
        self::assertEquals($collector->getUsedTypes(), $astCollector->getUsedTypes());
        self::assertEquals($collector->getUsedDirectives(), $astCollector->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, VariableDefinitionNode, ?Schema}>
     */
    public static function dataProviderSerialize(): array {
        $settings = (new TestSettings())
            ->setPrintDirectives(false);

        return [
            'type'                      => [
                <<<'STRING'
                $test: [Test!]!
                STRING,
                $settings,
                0,
                0,
                Parser::variableDefinition(
                    '$test: [Test!]!',
                ),
                null,
            ],
            'type + default'            => [
                <<<'STRING'
                $test: Test! = 123
                STRING,
                $settings,
                0,
                0,
                Parser::variableDefinition(
                    '$test: Test! = 123',
                ),
                null,
            ],
            'type + directives'         => [
                <<<'STRING'
                $test: Test!
                @a
                @b
                STRING,
                $settings
                    ->setPrintDirectives(true),
                0,
                0,
                Parser::variableDefinition(
                    '$test: Test! @a @b',
                ),
                null,
            ],
            'type + value + directives' => [
                <<<'STRING'
                $test: [String!] = [
                    "aaaaaaaaaaaaaaaaaaaaaaaaaa"
                    "bbbbbbbbbbbbbbbbbbbbbbbbbb"
                ]
                @a
                @b
                STRING,
                $settings
                    ->setPrintDirectives(true),
                0,
                120,
                Parser::variableDefinition(
                    '$test: [String!] = ["aaaaaaaaaaaaaaaaaaaaaaaaaa", "bbbbbbbbbbbbbbbbbbbbbbbbbb"] @a @b',
                ),
                null,
            ],
            'indent'                    => [
                <<<'STRING'
                $test: Test!
                    @a
                    @b
                STRING,
                $settings
                    ->setPrintDirectives(true),
                1,
                120,
                Parser::variableDefinition(
                    '$test: Test! @a @b',
                ),
                null,
            ],
            'filter: type (no schema)'  => [
                '$test: Test!',
                $settings
                    ->setTypeFilter(static function (): bool {
                        return false;
                    }),
                0,
                0,
                Parser::variableDefinition(
                    '$test: Test! @a @b',
                ),
                null,
            ],
            'filter: type'              => [
                '',
                $settings
                    ->setTypeFilter(static function (): bool {
                        return false;
                    }),
                0,
                0,
                Parser::variableDefinition(
                    '$test: Test! @a @b',
                ),
                BuildSchema::build(
                    <<<'STRING'
                    scalar Test
                    STRING,
                ),
            ],
            'filter: directive'         => [
                <<<'STRING'
                $test: Test!
                @a
                STRING,
                $settings
                    ->setPrintDirectives(true)
                    ->setDirectiveFilter(static function (string $directive): bool {
                        return $directive !== 'b';
                    }),
                0,
                0,
                Parser::variableDefinition(
                    '$test: Test! @a @b',
                ),
                null,
            ],
        ];
    }
    // </editor-fold>
}
