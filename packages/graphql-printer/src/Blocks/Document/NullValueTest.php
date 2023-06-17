<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\NullValueNode;
use GraphQL\Language\Parser;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(NullValue::class)]
class NullValueTest extends TestCase {
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
        NullValueNode $node,
    ): void {
        $context = new Context($settings, null, null);
        $actual  = (string) (new NullValue($context, $level, $used, $node));

        if ($expected) {
            Parser::valueLiteral($actual);
        }

        self::assertEquals($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, NullValueNode}>
     */
    public static function dataProviderToString(): array {
        $settings = (new TestSettings())
            ->setNormalizeArguments(false);

        return [
            NullValueNode::class => [
                'null',
                $settings,
                0,
                0,
                new NullValueNode([]),
            ],
        ];
    }
    // </editor-fold>
}
