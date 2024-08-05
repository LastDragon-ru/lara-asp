<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Reference;

use Countable;
use League\CommonMark\Reference\ReferenceInterface;
use League\CommonMark\Reference\ReferenceParser;
use ReflectionClass;
use ReflectionProperty;

use function count;
use function is_array;

/**
 * @internal
 */
class Parser {
    private ReferenceParser    $parser;
    private ReflectionProperty $parserReferences;
    private ReflectionProperty $parserState;
    private mixed              $parserStateParagraph;
    private mixed              $parserStateStartDefinition;

    public function __construct() {
        $class                            = new ReflectionClass(ReferenceParser::class);
        $this->parser                     = new ReferenceParser();
        $this->parserReferences           = $class->getProperty('references');
        $this->parserState                = $class->getProperty('state');
        $this->parserStateParagraph       = $class->getConstant('PARAGRAPH');
        $this->parserStateStartDefinition = $class->getConstant('START_DEFINITION');
    }

    public function parse(string $line): bool {
        // Parse
        $this->parser->parse($line ?: "\n");

        // Not a Reference
        if ($this->hasState($this->parserStateParagraph)) {
            return false;
        }

        // The previous is finished and the second started
        if ($this->getCount() > 0 && !$this->hasState($this->parserStateStartDefinition)) {
            return false;
        }

        // The previous and current finished
        if ($this->getCount() > 1) {
            return false;
        }

        // Ok
        return true;
    }

    public function getReference(): ?ReferenceInterface {
        $reference = null;

        foreach ($this->parser->getReferences() as $ref) {
            $reference = $ref;
            break;
        }

        return $reference;
    }

    private function hasState(mixed $state): bool {
        return $this->parserState->getValue($this->parser) === $state;
    }

    private function getCount(): int {
        $references = $this->parserReferences->getValue($this->parser);
        $count      = is_array($references) || $references instanceof Countable
            ? count($references)
            : 0;

        return $count;
    }
}
