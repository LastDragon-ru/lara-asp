<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Utils;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Contracts\LinkFactory;
use PhpParser\NameContext;
use PhpParser\Node\Name;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;

use function array_slice;
use function implode;
use function preg_replace_callback;
use function trim;

use const PREG_UNMATCHED_AS_NULL;

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
        return $this->node !== null && $this->node->getDeprecatedTagValues() !== [];
    }

    public function getDocument(LinkFactory $factory, NameContext $context, ?FilePath $path = null): Document {
        return new Document(
            trim(
                (string) preg_replace_callback(
                    pattern : '/\{@(?:see|link)\s+(?P<reference>[^}\s]+)\s?}/imu',
                    callback: static function (array $matches) use ($context, $factory): string {
                        $result    = $matches[0];
                        $reference = $factory->create(
                            $matches['reference'],
                            static function (string $class) use ($context): string {
                                return (string) $context->getResolvedClassName(new Name($class));
                            },
                        );

                        if ($reference !== null) {
                            $result = "`{$reference}`";
                        }

                        return $result;
                    },
                    subject : $this->getText(),
                    flags   : PREG_UNMATCHED_AS_NULL,
                ),
            ),
            $path,
        );
    }

    /**
     * @param array<array-key, string> $strings
     */
    private function join(array $strings): string {
        return implode($this->eol, $strings);
    }

    private function parse(?string $comment): ?PhpDocNode {
        // Empty?
        if ($comment === null || trim($comment) === '') {
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
