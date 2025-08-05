<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type as GraphQLType;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use LastDragon_ru\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\GraphQLPrinter\Misc\Context;
use LastDragon_ru\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\GraphQLPrinter\Testing\TestSettings;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(Type::class)]
final class TypeTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param (TypeNode&Node)|(GraphQLType&InputType)|(GraphQLType&OutputType) $type fixme(phpcs): https://github.com/slevomat/coding-standard/issues/1690
     */
    #[DataProvider('dataProviderSerialize')]
    public function testSerialize(
        string $expected,
        Settings $settings,
        int $level,
        int $used,
        (TypeNode&Node)|(GraphQLType&OutputType)|(GraphQLType&InputType) $type,
        ?Schema $schema,
    ): void {
        $collector = new Collector();
        $context   = new Context($settings, null, $schema);
        $actual    = (new Type($context, $type))->serialize($collector, $level, $used);

        self::assertSame($expected, $actual);
    }

    public function testStatistics(): void {
        $node      = new NonNull(
            new ObjectType([
                'name'   => 'Test',
                'fields' => [
                    'field' => [
                        'type' => GraphQLType::string(),
                    ],
                ],
            ]),
        );
        $context   = new Context(new TestSettings(), null, null);
        $collector = new Collector();
        $block     = new Type($context, $node);
        $type      = $node->getInnermostType()->name();
        $content   = $block->serialize($collector, 0, 0);

        self::assertNotEmpty($content);
        self::assertEquals([$type => $type], $collector->getUsedTypes());
        self::assertEquals([], $collector->getUsedDirectives());

        $astCollector = new Collector();
        $astBlock     = new Type($context, Parser::typeReference($content));

        self::assertEquals($content, $astBlock->serialize($astCollector, 0, 0));
        self::assertEquals($collector->getUsedTypes(), $astCollector->getUsedTypes());
        self::assertEquals($collector->getUsedDirectives(), $astCollector->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string,Settings,int,int,(TypeNode&Node)|(GraphQLType&(OutputType|InputType)),?Schema}>
     */
    public static function dataProviderSerialize(): array {
        $settings = new TestSettings();
        $type     = new ObjectType([
            'name'   => 'Test',
            'fields' => [
                'field' => [
                    'type' => GraphQLType::string(),
                ],
            ],
        ]);

        return [
            'type: object'            => [
                'Test',
                $settings,
                0,
                0,
                $type,
                null,
            ],
            'type: non null'          => [
                'Test!',
                $settings,
                0,
                0,
                new NonNull($type),
                null,
            ],
            'type: non null list'     => [
                '[Test]!',
                $settings,
                0,
                0,
                new NonNull(new ListOfType($type)),
                null,
            ],
            'filter (no schema)'      => [
                'Test',
                $settings
                    ->setTypeFilter(static fn () => false),
                0,
                0,
                $type,
                null,
            ],
            'filter'                  => [
                '',
                $settings
                    ->setTypeFilter(static fn () => false),
                0,
                0,
                new NonNull(new ListOfType($type)),
                BuildSchema::build(
                    <<<'GRAPHQL'
                    scalar Test
                    GRAPHQL,
                ),
            ],
            'ast: object'             => [
                'Test',
                $settings,
                0,
                0,
                Parser::typeReference('Test'),
                null,
            ],
            'ast: non null'           => [
                'Test!',
                $settings,
                0,
                0,
                Parser::typeReference('Test!'),
                null,
            ],
            'ast: non null list'      => [
                '[Test]!',
                $settings,
                0,
                0,
                Parser::typeReference('[Test]!'),
                null,
            ],
            'ast: filter (no schema)' => [
                '[Test]!',
                $settings
                    ->setTypeFilter(static fn () => false),
                0,
                0,
                Parser::typeReference('[Test]!'),
                null,
            ],
            'ast: filter'             => [
                '',
                $settings
                    ->setTypeFilter(static fn () => false),
                0,
                0,
                Parser::typeReference('[Test]!'),
                BuildSchema::build(
                    <<<'GRAPHQL'
                    scalar Test
                    GRAPHQL,
                ),
            ],
        ];
    }
    // </editor-fold>
}
