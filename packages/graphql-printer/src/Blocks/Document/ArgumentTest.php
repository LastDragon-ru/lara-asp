<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\ArgumentNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Argument::class)]
class ArgumentTest extends TestCase {
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
        ArgumentNode $argumentNode,
        ?Type $argumentType,
        ?Schema $schema,
    ): void {
        $collector = new Collector();
        $context   = new Context($settings, null, $schema);
        $actual    = (new Argument($context, $argumentNode, $argumentType))->serialize($collector, $level, $used);

        if ($expected) {
            Parser::argument($actual);
        }

        self::assertEquals($expected, $actual);
    }

    public function testStatistics(): void {
        $context    = new Context(new TestSettings(), null, null);
        $collector  = new Collector();
        $definition = Parser::argument('test: 123');
        $block      = new Argument($context, $definition, Type::int());
        $content    = $block->serialize($collector, 0, 0);

        self::assertNotEmpty($content);
        self::assertEquals(['Int' => 'Int'], $collector->getUsedTypes());
        self::assertEquals([], $collector->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, ArgumentNode, ?Type, ?Schema}>
     */
    public static function dataProviderSerialize(): array {
        $settings = new TestSettings();

        return [
            'argument'                    => [
                <<<'STRING'
                c: {
                    a: 123
                }
                STRING,
                $settings,
                0,
                0,
                Parser::argument('c: {a: 123}'),
                null,
                null,
            ],
            'argument (level)'            => [
                <<<'STRING'
                c: {
                        a: 123
                    }
                STRING,
                $settings,
                1,
                0,
                Parser::argument('c: {a: 123}'),
                null,
                null,
            ],
            'filter => false (no schema)' => [
                'a: 123',
                $settings
                    ->setTypeFilter(static fn (string $name) => $name !== Type::INT),
                0,
                0,
                Parser::argument('a: 123'),
                Type::int(),
                null,
            ],
            'filter => false'             => [
                '',
                $settings
                    ->setTypeFilter(static fn (string $name) => $name !== Type::INT),
                0,
                0,
                Parser::argument('a: 123'),
                Type::int(),
                BuildSchema::build(
                    <<<'STRING'
                    scalar A
                    STRING,
                ),
            ],
            'filter => true'              => [
                'b: "abc"',
                $settings
                    ->setTypeFilter(static fn (string $name) => $name !== Type::INT),
                0,
                0,
                Parser::argument('b: "abc"'),
                Type::string(),
                BuildSchema::build(
                    <<<'STRING'
                    scalar A
                    STRING,
                ),
            ],
            'filter => unknown'           => [
                'c: "abc"',
                $settings
                    ->setTypeFilter(static fn (string $name) => $name !== Type::INT),
                0,
                0,
                Parser::argument('c: "abc"'),
                null,
                BuildSchema::build(
                    <<<'STRING'
                    scalar A
                    STRING,
                ),
            ],
        ];
    }
    // </editor-fold>
}
