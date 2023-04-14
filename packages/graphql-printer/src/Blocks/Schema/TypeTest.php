<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Schema;

use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type as GraphQLType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Schema\Type
 */
class TypeTest extends TestCase {
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
        GraphQLType $type,
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
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, GraphQLType}>
     */
    public static function dataProviderToString(): array {
        $settings = new TestSettings();
        $object   = new ObjectType([
            'name'   => 'Test',
            'fields' => [
                'field' => [
                    'type' => GraphQLType::string(),
                ],
            ],
        ]);

        return [
            'object'        => [
                'Test',
                $settings,
                0,
                0,
                $object,
            ],
            'non null'      => [
                'Test!',
                $settings,
                0,
                0,
                new NonNull($object),
            ],
            'non null list' => [
                '[Test]!',
                $settings,
                0,
                0,
                new NonNull(new ListOfType($object)),
            ],
        ];
    }
    // </editor-fold>
}
