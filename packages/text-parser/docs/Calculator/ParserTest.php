<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Docs\Calculator;

use LastDragon_ru\TextParser\Docs\Calculator\Ast\ExpressionNode;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\NumberNode;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\OperatorAdditionNode;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\OperatorDivisionNode;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\OperatorMultiplicationNode;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\OperatorSubtractionNode;
use LastDragon_ru\TextParser\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(Parser::class)]
final class ParserTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderParse')]
    public function testParse(?ExpressionNode $expected, string $expression): void {
        self::assertEquals($expected, (new Parser())->parse($expression));
    }
    // </editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    /**
     * @return array<string, array{?ExpressionNode, string}>
     */
    public static function dataProviderParse(): array {
        return [
            'Number'                             => [
                new ExpressionNode([
                    new NumberNode(123),
                ]),
                '123',
            ],
            'Numbers and Operator'               => [
                new ExpressionNode([
                    new NumberNode(1),
                    new OperatorAdditionNode(),
                    new NumberNode(2),
                    new OperatorSubtractionNode(),
                    new NumberNode(3),
                ]),
                '1 + 2 - 3',
            ],
            'Sub-expression'                     => [
                new ExpressionNode([
                    new NumberNode(1),
                    new OperatorAdditionNode(),
                    new ExpressionNode([
                        new NumberNode(2),
                        new OperatorSubtractionNode(),
                        new NumberNode(3),
                        new OperatorAdditionNode(),
                        new ExpressionNode([
                            new NumberNode(4),
                            new OperatorMultiplicationNode(),
                            new NumberNode(5),
                        ]),
                    ]),
                    new OperatorDivisionNode(),
                    new NumberNode(2),
                ]),
                '1 + (2 - 3 + (4 * 5)) / 2',
            ],
            'Sub-expressions should be balanced' => [
                null,
                '1 + (2 - 3',
            ],
            'Not mathematical expression'        => [
                null,
                '1 + abc',
            ],
            'Operator is missing'                => [
                null,
                '1 1',
            ],
        ];
    }
    // </editor-fold>
}
