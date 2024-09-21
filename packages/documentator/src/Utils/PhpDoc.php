<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Utils;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;

use function array_slice;
use function implode;
use function trim;

/**
 * @internal
 */
class PhpDoc {
    private readonly ?PhpDocNode $node;

    /**
     * @var list<string>
     */
    private readonly array $text;

    public function __construct(
        private readonly ?string $comment,
        private readonly string $eol = "\n\n",
    ) {
        $this->node = $this->parse($this->comment);
        $this->text = $this->getTextNodes($this->node);
    }

    public function getText(): string {
        return $this->join($this->text);
    }

    public function getSummary(): string {
        return $this->text[0] ?? '';
    }

    public function getDescription(): string {
        return $this->join(array_slice($this->text, 1));
    }

    public function isDeprecated(): bool {
        return $this->node && $this->node->getDeprecatedTagValues() !== [];
    }

    /**
     * @param array<array-key, string> $strings
     */
    private function join(array $strings): string {
        return implode($this->eol, $strings);
    }

    private function parse(?string $comment): ?PhpDocNode {
        // Empty?
        if (!$comment || trim($comment) === '') {
            return null;
        }

        // Parse
        $lexer  = new Lexer();
        $parser = new PhpDocParser(new TypeParser(new ConstExprParser()), new ConstExprParser());
        $tokens = new TokenIterator($lexer->tokenize($comment));
        $node   = $parser->parse($tokens);

        // Return
        return $node;
    }

    /**
     * @return list<string>
     */
    private function getTextNodes(?PhpDocNode $node): array {
        $nodes = [];

        foreach (($node->children ?? []) as $child) {
            if ($child instanceof PhpDocTextNode) {
                if (trim($child->text) !== '') {
                    $nodes[] = $child->text;
                }
            } else {
                break;
            }
        }

        return $nodes;
    }
}
