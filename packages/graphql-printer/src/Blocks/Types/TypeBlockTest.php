<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types;

use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\TypeBlock
 */
class TypeBlockTest extends TestCase {
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
        Type $type,
    ): void {
        $actual = (string) (new TypeBlock($settings, $level, $used, $type));

        self::assertEquals($expected, $actual);
    }

    public function testStatistics(): void {
        $node     = new NonNull(
            new ObjectType([
                'name' => 'Test',
            ]),
        );
        $settings = new TestSettings();
        $block    = new TypeBlock($settings, 0, 0, $node);
        $type     = $node->getWrappedType(true)->name;

        self::assertNotEmpty((string) $block);
        self::assertEquals([$type => $type], $block->getUsedTypes());
        self::assertEquals([], $block->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, Type}>
     */
    public function dataProviderToString(): array {
        $settings = new TestSettings();

        return [
            'object'        => [
                'Test',
                $settings,
                0,
                0,
                new ObjectType([
                    'name' => 'Test',
                ]),
            ],
            'non null'      => [
                'Test!',
                $settings,
                0,
                0,
                new NonNull(
                    new ObjectType([
                        'name' => 'Test',
                    ]),
                ),
            ],
            'non null list' => [
                '[Test]!',
                $settings,
                0,
                0,
                new NonNull(
                    new ListOfType(
                        new ObjectType([
                            'name' => 'Test',
                        ]),
                    ),
                ),
            ],
        ];
    }
    // </editor-fold>
}
