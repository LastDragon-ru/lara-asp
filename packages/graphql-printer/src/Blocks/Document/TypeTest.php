<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type as GraphQLType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Type::class)]
class TypeTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderToString
     *
     * @param (TypeNode&Node)|GraphQLType $type
     */
    public function testToString(
        string $expected,
        Settings $settings,
        int $level,
        int $used,
        TypeNode|GraphQLType $type,
    ): void {
        $context = new Context($settings, null, null);
        $actual  = (string) (new Type($context, $level, $used, $type));

        self::assertEquals($expected, $actual);
    }

    public function testStatistics(): void {
        $node    = new NonNull(
            new ObjectType([
                'name'   => 'Test',
                'fields' => [
                    'field' => [
                        'type' => GraphQLType::string(),
                    ],
                ],
            ]),
        );
        $context = new Context(new TestSettings(), null, null);
        $block   = new Type($context, 0, 0, $node);
        $type    = $node->getInnermostType()->name();

        self::assertNotEmpty((string) $block);
        self::assertEquals([$type => $type], $block->getUsedTypes());
        self::assertEquals([], $block->getUsedDirectives());

        $ast = new Type($context, 0, 0, Parser::typeReference((string) $block));

        self::assertEquals($block->getUsedTypes(), $ast->getUsedTypes());
        self::assertEquals($block->getUsedDirectives(), $ast->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, (TypeNode&Node)|GraphQLType}>
     */
    public static function dataProviderToString(): array {
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
            'type: object'        => [
                'Test',
                $settings,
                0,
                0,
                $type,
            ],
            'type: non null'      => [
                'Test!',
                $settings,
                0,
                0,
                new NonNull($type),
            ],
            'type: non null list' => [
                '[Test]!',
                $settings,
                0,
                0,
                new NonNull(new ListOfType($type)),
            ],
            'ast: object'         => [
                'Test',
                $settings,
                0,
                0,
                Parser::typeReference('Test'),
            ],
            'ast: non null'       => [
                'Test!',
                $settings,
                0,
                0,
                Parser::typeReference('Test!'),
            ],
            'ast: non null list'  => [
                '[Test]!',
                $settings,
                0,
                0,
                Parser::typeReference('[Test]!'),
            ],
        ];
    }
    // </editor-fold>
}
