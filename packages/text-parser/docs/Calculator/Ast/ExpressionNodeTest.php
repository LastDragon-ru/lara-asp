<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Docs\Calculator\Ast;

use LastDragon_ru\TextParser\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function is_nan;

use const NAN;

/**
 * @internal
 */
#[CoversClass(ExpressionNode::class)]
final class ExpressionNodeTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param list<ExpressionNodeChild> $children
     */
    #[DataProvider('dataProviderCalculate')]
    public function testCalculate(float|int $expected, array $children): void {
        $actual = (new ExpressionNode($children))->calculate();

        if (!is_nan($expected)) {
            self::assertSame($expected, $actual);
        } else {
            self::assertNan($actual);
        }
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{float|int, list<ExpressionNodeChild>}>
     */
    public static function dataProviderCalculate(): array {
        return [
            '1 + 1 = 2'      => [
                2,
                [
                    new NumberNode(1),
                    new OperatorAdditionNode(),
                    new NumberNode(1),
                ],
            ],
            '10 - 2 * 3 = 6' => [
                4,
                [
                    new NumberNode(10),
                    new OperatorSubtractionNode(),
                    new NumberNode(2),
                    new OperatorMultiplicationNode(),
                    new NumberNode(3),
                ],
            ],
            '- 2 - 2 = -4'   => [
                -4,
                [
                    new OperatorSubtractionNode(),
                    new NumberNode(2),
                    new OperatorSubtractionNode(),
                    new NumberNode(2),
                ],
            ],
            '- = NAN'        => [
                NAN,
                [
                    new OperatorSubtractionNode(),
                ],
            ],
            '2 2 = NAN'      => [
                NAN,
                [
                    new NumberNode(2),
                    new NumberNode(2),
                ],
            ],
        ];
    }
    // </editor-fold>
}
