<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Parser;

use LastDragon_ru\DiyParser\Iterables\TransactionalIterable;
use LastDragon_ru\DiyParser\Package as ParserPackage;
use LastDragon_ru\DiyParser\Tokenizer\Token;
use LastDragon_ru\DiyParser\Tokenizer\Tokenizer;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\BraceExpansionNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\BraceExpansionNodeChild;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\CharacterSequenceNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\IncrementalSequenceNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\IntegerSequenceNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\SequenceNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\StringNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Parser\Factories\BraceExpansionNodeFactory;
use LastDragon_ru\GlobMatcher\BraceExpander\Parser\Factories\SequenceNodeFactory;

use function filter_var;
use function mb_ltrim;
use function mb_strlen;
use function mb_strpos;
use function mb_substr;
use function preg_match;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_INT;

class Parser {
    public function __construct() {
        // empty
    }

    public function parse(string $pattern): ?BraceExpansionNode {
        // TODO(glob-matcher): Better limits (count/max length/etc)
        // TODO(glob-matcher): Error if transaction is not finished?
        $iterable = (new Tokenizer(Name::class, Name::Backslash))->tokenize([$pattern]);
        $node     = $this->parseBraceExpansion($iterable);

        return $node;
    }

    /**
     * @param iterable<mixed, Token<Name>> $iterable
     */
    protected function parseBraceExpansion(iterable $iterable): ?BraceExpansionNode {
        $iterable = new TransactionalIterable($iterable, 4096, 5);
        $factory  = new BraceExpansionNodeFactory();

        while ($iterable->valid()) {
            $factory->push($this->parseBraceExpansionChild($iterable));
        }

        return $factory->create();
    }

    /**
     * @param TransactionalIterable<Token<Name>> $iterable
     */
    protected function parseBraceExpansionChild(TransactionalIterable $iterable): ?BraceExpansionNodeChild {
        return $this->parseIncrementalSequence($iterable)
            ?? $this->parseSequence($iterable)
            ?? $this->parseString($iterable);
    }

    /**
     * @param TransactionalIterable<Token<Name>> $iterable
     */
    protected function parseIncrementalSequence(TransactionalIterable $iterable): ?IncrementalSequenceNode {
        // Is `{`?
        if ($iterable[0]?->is(Name::LeftCurlyBracket) !== true) {
            return null;
        }

        // Begin
        $iterable->begin();
        $iterable->next();

        // Start
        $start          = $iterable[0]?->is(null) === true ? (string) $iterable[0] : '';
        $startInteger   = $this->isInteger($start) ? $start : null;
        $startCharacter = $startInteger === null && $this->isCharacter($start) ? $start : null;

        if ($iterable[1]?->is(Name::DoubleDot) === true && ($startInteger !== null || $startCharacter !== null)) {
            $iterable->next(2);
        } else {
            $iterable->rollback();

            return null;
        }

        // End
        $end          = $iterable[0]?->is(null) === true ? (string) $iterable[0] : '';
        $endInteger   = $this->isInteger($end) ? $end : null;
        $endCharacter = $endInteger === null && $this->isCharacter($end) ? $end : null;

        if (($startInteger !== null && $endInteger !== null) || ($startCharacter !== null && $endCharacter !== null)) {
            $iterable->next();
        } else {
            $iterable->rollback();

            return null;
        }

        // Increment
        $increment = null;

        if ($iterable[0]?->is(Name::DoubleDot) === true) {
            $increment = $iterable[1]?->is(null) === true ? (string) $iterable[1] : '';
            $increment = mb_ltrim($increment, '0', ParserPackage::Encoding);
            $increment = $increment !== '' ? $increment : '0';
            $increment = filter_var($increment, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

            if ($increment !== null) {
                $iterable->next(2);
            } else {
                $iterable->rollback();

                return null;
            }
        }

        // Is `}`?
        if ($iterable[0]?->is(Name::RightCurlyBracket) === true) {
            $iterable->next();
        } else {
            $iterable->rollback();

            return null;
        }

        // Create
        $node = match (true) {
            $startCharacter !== null && $endCharacter !== null
            => new CharacterSequenceNode($startCharacter, $endCharacter, $increment),
            $startInteger !== null && $endInteger !== null
            => new IntegerSequenceNode($startInteger, $endInteger, $increment),
            default
            => null,
        };

        // Commit
        $iterable->end($node);

        // Return
        return $node;
    }

    /**
     * @param TransactionalIterable<Token<Name>> $iterable
     */
    protected function parseSequence(TransactionalIterable $iterable): ?SequenceNode {
        // Is `{`?
        if ($iterable[0]?->is(Name::LeftCurlyBracket) !== true) {
            return null;
        }

        // Begin
        $iterable->begin();
        $iterable->next();

        // Parse
        $node             = null;
        $hasComma         = false;
        $hasDoubleDot     = false;
        $sequenceFactory  = new SequenceNodeFactory();
        $expansionFactory = new BraceExpansionNodeFactory();

        while ($iterable->valid()) {
            // Is comma?
            if ($iterable[0]?->is(Name::Comma) === true) {
                $sequenceFactory->push($expansionFactory->create());
                $iterable->next();

                $hasComma = true;

                continue;
            }

            // Is `}`?
            if ($iterable[0]?->is(Name::RightCurlyBracket) === true) {
                // If `..` and no comma -> it is a malformed incremental sequence,
                // and it should be parsed as a string.
                if (!$hasDoubleDot || $hasComma) {
                    $sequenceFactory->push($expansionFactory->create());

                    $node = $sequenceFactory->create();

                    $iterable->next();
                }

                break;
            }

            // Is `..`?
            if ($iterable[0]?->is(Name::DoubleDot) === true) {
                $hasDoubleDot = true;
            }

            // Child
            $expansionFactory->push(
                $this->parseBraceExpansionChild($iterable),
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
    protected function parseString(TransactionalIterable $iterable): ?StringNode {
        $node = null;

        if ($iterable[0] !== null) {
            $string = (string) $iterable[0];

            if ($string !== '') {
                $node = new StringNode($string);
            }

            $iterable->next();
        }

        return $node;
    }

    /**
     * @phpstan-assert-if-true numeric-string $string
     */
    private function isInteger(string $string): bool {
        if (preg_match('/^-?[0-9]+$/u', $string) !== 1) {
            return false;
        }

        $string = mb_strpos($string, '-', 0, ParserPackage::Encoding) === 0
            ? mb_substr($string, 1, null, ParserPackage::Encoding)
            : $string;
        $string = mb_ltrim($string, '0', ParserPackage::Encoding);
        $string = $string !== '' ? $string : '0';
        $string = filter_var($string, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

        return $string !== null;
    }

    /**
     * @phpstan-assert-if-true non-empty-string $string
     */
    private function isCharacter(string $string): bool {
        return mb_strlen($string, ParserPackage::Encoding) === 1;
    }
}
