<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Docs\Calculator;

use LastDragon_ru\TextParser\Docs\Calculator\Ast\ExpressionNode;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\ExpressionNodeChild;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\ExpressionNodeFactory;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\NumberNode;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\OperatorAdditionNode;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\OperatorDivisionNode;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\OperatorMultiplicationNode;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\OperatorNode;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\OperatorSubtractionNode;
use LastDragon_ru\TextParser\Iterables\TransactionalIterable;
use LastDragon_ru\TextParser\Tokenizer\Token;
use LastDragon_ru\TextParser\Tokenizer\Tokenizer;
use LogicException;

use function filter_var;
use function preg_match;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_INT;

class Parser {
    public function __construct() {
        // empty
    }

    public function parse(string $pattern): ?ExpressionNode {
        $node = null;

        try {
            $iterable = (new Tokenizer(Name::class))->tokenize([$pattern]);
            $node     = $this->parseExpression($iterable);
        } catch (LogicException) {
            // The `$pattern` is not a valid expression
        }

        return $node;
    }

    /**
     * @param iterable<mixed, Token<Name>> $iterable
     */
    protected function parseExpression(iterable $iterable): ?ExpressionNode {
        $iterable = new TransactionalIterable($iterable, 64, 1);
        $factory  = new ExpressionNodeFactory();

        while ($iterable->valid()) {
            $factory->push($this->parseExpressionChild($iterable));
        }

        return $factory->create();
    }

    /**
     * @param TransactionalIterable<Token<Name>> $iterable
     */
    protected function parseExpressionChild(TransactionalIterable $iterable): ?ExpressionNodeChild {
        return $this->parseSubExpression($iterable)
            ?? $this->parseOperator($iterable)
            ?? $this->parseNumber($iterable)
            ?? $this->parseSpace($iterable);
    }

    /**
     * @param TransactionalIterable<Token<Name>> $iterable
     */
    protected function parseSubExpression(TransactionalIterable $iterable): ?ExpressionNode {
        // Is `(`?
        if ($iterable[0]?->is(Name::LeftParenthesis) !== true) {
            return null;
        }

        // Begin
        $iterable->begin();
        $iterable->next();

        // Parse
        $node    = null;
        $factory = new ExpressionNodeFactory();

        while ($iterable->valid()) {
            // Is `)`?
            if ($iterable[0]?->is(Name::RightParenthesis) === true) {
                $node = $factory->create();

                $iterable->next();

                break;
            }

            // Child
            $factory->push(
                $this->parseExpressionChild($iterable),
            );
        }

        // Commit
        $iterable->end($node);

        // Return
        return $node;
    }

    /**
     * @param TransactionalIterable<Token<Name>> $iterable
     */
    protected function parseOperator(TransactionalIterable $iterable): ?OperatorNode {
        $node = match ($iterable[0]->name ?? null) {
            Name::Plus     => new OperatorAdditionNode(),
            Name::Minus    => new OperatorSubtractionNode(),
            Name::Asterisk => new OperatorMultiplicationNode(),
            Name::Slash    => new OperatorDivisionNode(),
            default        => null,
        };

        if ($node !== null) {
            $iterable->next();
        }

        return $node;
    }

    /**
     * @param TransactionalIterable<Token<Name>> $iterable
     */
    protected function parseNumber(TransactionalIterable $iterable): ?NumberNode {
        // String?
        if ($iterable[0]?->is(null) !== true) {
            return null;
        }

        // Number?
        $number = $iterable[0]->value;
        $number = preg_match('/^[0-9]+$/u', $number) === 1
            ? filter_var($number, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE)
            : null;
        $node   = $number !== null ? new NumberNode($number) : null;

        if ($node !== null) {
            $iterable->next();
        }

        // Return
        return $node;
    }

    /**
     * @param TransactionalIterable<Token<Name>> $iterable
     */
    protected function parseSpace(TransactionalIterable $iterable): null {
        // Only spaces allowed here
        if ($iterable[0]?->is(Name::Space) !== true) {
            throw new LogicException('The string is not a mathematical expression.');
        } else {
            $iterable->next();
        }

        return null;
    }
}
