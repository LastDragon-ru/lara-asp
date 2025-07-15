<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Parser;

use Closure;
use LastDragon_ru\DiyParser\Iterables\TransactionalIterable;
use LastDragon_ru\DiyParser\Tokenizer\Token;
use LastDragon_ru\GlobMatcher\Ast\Nodes\AsteriskNode;
use LastDragon_ru\GlobMatcher\Ast\Nodes\CharacterClass;
use LastDragon_ru\GlobMatcher\Ast\Nodes\CharacterClassNode;
use LastDragon_ru\GlobMatcher\Ast\Nodes\CharacterCollatingSymbolNode;
use LastDragon_ru\GlobMatcher\Ast\Nodes\CharacterEquivalenceClassNode;
use LastDragon_ru\GlobMatcher\Ast\Nodes\CharacterNode;
use LastDragon_ru\GlobMatcher\Ast\Nodes\CharacterNodeChild;
use LastDragon_ru\GlobMatcher\Ast\Nodes\GlobNode;
use LastDragon_ru\GlobMatcher\Ast\Nodes\GlobNodeChild;
use LastDragon_ru\GlobMatcher\Ast\Nodes\GlobstarNode;
use LastDragon_ru\GlobMatcher\Ast\Nodes\NameNode;
use LastDragon_ru\GlobMatcher\Ast\Nodes\NameNodeChild;
use LastDragon_ru\GlobMatcher\Ast\Nodes\QuestionNode;
use LastDragon_ru\GlobMatcher\Ast\Nodes\SegmentNode;
use LastDragon_ru\GlobMatcher\Ast\Nodes\StringNode;
use LastDragon_ru\GlobMatcher\Options;
use LastDragon_ru\GlobMatcher\Parser\Factories\CharacterNodeFactory;
use LastDragon_ru\GlobMatcher\Parser\Factories\GlobNodeFactory;
use LastDragon_ru\GlobMatcher\Parser\Factories\NameNodeFactory;

class Parser {
    public function __construct(
        protected Options $options,
    ) {
        // empty
    }

    public function parse(string $pattern): ?GlobNode {
        // TODO(documentator/glob): Better limits (count/max length/etc)
        $iterable = (new Tokenizer())->tokenize([$pattern]);
        $glob     = $this->parseGlob($iterable);

        return $glob;
    }

    /**
     * @param iterable<mixed, Token<Name>> $iterable
     */
    protected function parseGlob(iterable $iterable): ?GlobNode {
        $iterable = new TransactionalIterable($iterable, 1, 3);
        $factory  = new GlobNodeFactory();
        $pattern  = [];

        while ($iterable->valid()) {
            $child = $this->parseGlobChild($iterable);

            if ($child !== null) {
                $factory->push($this->parseName($pattern));
                $factory->push($child);

                $pattern = [];
            } else {
                $pattern[] = $iterable->current();

                $iterable->next();
            }
        }

        $factory->push($this->parseName($pattern));

        return $factory->create();
    }

    /**
     * @param TransactionalIterable<Token<Name>> $iterable
     */
    protected function parseGlobChild(TransactionalIterable $iterable): ?GlobNodeChild {
        return $this->parseGlobstar($iterable)
            ?? $this->parseSegment($iterable);
    }

    /**
     * @param TransactionalIterable<Token<Name>> $iterable
     */
    protected function parseGlobstar(TransactionalIterable $iterable): ?GlobstarNode {
        // Enabled?
        if (!$this->options->globstar) {
            return null;
        }

        // Globstar?
        if (
            $iterable[0]?->is(Name::Asterisk) !== true
            || $iterable[1]?->is(Name::Asterisk) !== true
            || ($iterable[2]?->is(Name::Slash) ?? true) !== true
        ) {
            return null;
        }

        if (($iterable[-1]?->is(Name::Slash) ?? true) !== true) {
            return null;
        }

        // Skip **
        $iterable->next(2);

        // Skip trailing `/` if it is not the last of the stream
        if ($iterable[0]?->is(Name::Slash) === true && $iterable[1] !== null) {
            $iterable->next();
        }

        // Return
        return new GlobstarNode();
    }

    /**
     * @param TransactionalIterable<Token<Name>> $iterable
     */
    protected function parseSegment(TransactionalIterable $iterable): ?SegmentNode {
        $node = null;

        if ($iterable[0]?->is(Name::Slash) === true) {
            $node = new SegmentNode();

            $iterable->next();
        }

        return $node;
    }

    /**
     * @param iterable<mixed, Token<Name>> $iterable
     */
    protected function parseName(iterable $iterable): ?NameNode {
        $iterable = new TransactionalIterable($iterable, 4096, 5);
        $factory  = new NameNodeFactory();

        while ($iterable->valid()) {
            $factory->push($this->parseNameChild($iterable));
        }

        return $factory->create();
    }

    /**
     * @param TransactionalIterable<Token<Name>> $iterable
     */
    protected function parseNameChild(TransactionalIterable $iterable): ?NameNodeChild {
        return $this->parseCharacter($iterable)
            ?? $this->parseAsterisk($iterable)
            ?? $this->parseQuestion($iterable)
            ?? $this->parseString($iterable);
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
     * @param TransactionalIterable<Token<Name>> $iterable
     */
    protected function parseQuestion(TransactionalIterable $iterable): ?QuestionNode {
        $node = null;

        if ($iterable[0]?->is(Name::Question) === true) {
            $node = new QuestionNode();

            $iterable->next();
        }

        return $node;
    }

    /**
     * @param TransactionalIterable<Token<Name>> $iterable
     */
    protected function parseAsterisk(TransactionalIterable $iterable): ?AsteriskNode {
        $node = null;

        if ($iterable[0]?->is(Name::Asterisk) === true) {
            $node = new AsteriskNode();

            $iterable->next();
        }

        return $node;
    }

    /**
     * @param TransactionalIterable<Token<Name>> $iterable
     */
    protected function parseCharacter(TransactionalIterable $iterable): ?CharacterNode {
        // Match?
        if ($iterable[0]?->is(Name::LeftSquareBracket) !== true) {
            return null;
        }

        // Begin
        $iterable->begin();
        $iterable->next();

        // Negated?
        $negated = false;

        if ($iterable[0]?->is(Name::ExclamationMark) === true || $iterable[0]?->is(Name::Circumflex) === true) {
            $negated = true;

            $iterable->next();
        }

        // Parse
        $node    = null;
        $factory = new CharacterNodeFactory($negated);

        while ($iterable->valid()) {
            // End?
            if ($iterable[0]?->is(Name::RightSquareBracket) === true && !$factory->isEmpty()) {
                $node = $factory->create();

                $iterable->next();

                break;
            }

            // Child
            $factory->push(
                $this->parseCharacterClass($iterable)
                ?? $this->parseCharacterCollatingSymbol($iterable)
                ?? $this->parseCharacterCharacterEquivalenceClass($iterable)
                ?? $this->parseString($iterable),
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
    protected function parseCharacterClass(TransactionalIterable $iterable): ?CharacterClassNode {
        return $this->parseCharacterBrackets(
            $iterable,
            Name::Colon,
            static function (string $class): ?CharacterClassNode {
                $class = CharacterClass::tryFrom($class);
                $node  = $class !== null
                    ? new CharacterClassNode($class)
                    : null;

                return $node;
            },
        );
    }

    /**
     * @param TransactionalIterable<Token<Name>> $iterable
     */
    protected function parseCharacterCollatingSymbol(TransactionalIterable $iterable): ?CharacterCollatingSymbolNode {
        return $this->parseCharacterBrackets(
            $iterable,
            Name::Dot,
            static function (string $symbol): CharacterCollatingSymbolNode {
                return new CharacterCollatingSymbolNode($symbol);
            },
        );
    }

    /**
     * @param TransactionalIterable<Token<Name>> $iterable
     */
    protected function parseCharacterCharacterEquivalenceClass(
        TransactionalIterable $iterable,
    ): ?CharacterEquivalenceClassNode {
        return $this->parseCharacterBrackets(
            $iterable,
            Name::Equal,
            static function (string $class): CharacterEquivalenceClassNode {
                return new CharacterEquivalenceClassNode($class);
            },
        );
    }

    /**
     * @template T of CharacterNodeChild
     *
     * @param TransactionalIterable<Token<Name>> $iterable
     * @param Closure(string): ?T                $factory
     *
     * @return T
     */
    private function parseCharacterBrackets(
        TransactionalIterable $iterable,
        Name $name,
        Closure $factory,
    ): ?CharacterNodeChild {
        // Match?
        if (!($iterable[0]?->is(Name::LeftSquareBracket) === true && $iterable[1]?->is($name) === true)) {
            return null;
        }

        // Begin
        $iterable->begin();
        $iterable->next(2);

        // Join
        $string = '';

        while ($iterable->valid()) {
            if (!($iterable[0]?->is($name) === true && $iterable[1]?->is(Name::RightSquareBracket) === true)) {
                $string .= $iterable[0];

                $iterable->next();
            } else {
                $iterable->next(2);

                break;
            }
        }

        // Cannot be empty and/or the last node in the stream
        if ($string === '' || !$iterable->valid()) {
            $iterable->rollback();

            return null;
        }

        // End
        $node = $factory($string);

        $iterable->end($node);

        // Return
        return $node;
    }
}
