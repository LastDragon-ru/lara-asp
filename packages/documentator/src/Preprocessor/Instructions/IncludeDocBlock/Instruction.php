<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeDocBlock;

use Exception;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\ParameterizableInstruction;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TargetIsNotFile;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TargetIsNotValidPhpFile;
use LastDragon_ru\LaraASP\Documentator\Utils\Path;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;
use Override;
use PhpParser\NameContext;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode as PhpDocBlockNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;

use function array_slice;
use function dirname;
use function file_get_contents;
use function implode;
use function preg_replace_callback;
use function trim;

use const PREG_UNMATCHED_AS_NULL;

/**
 * @implements ParameterizableInstruction<Parameters>
 */
class Instruction implements ParameterizableInstruction {
    public function __construct() {
        // empty
    }

    #[Override]
    public static function getName(): string {
        return 'include:docblock';
    }

    #[Override]
    public static function getDescription(): string {
        return <<<'DESC'
            Includes the docblock of the first PHP class/interface/trait/enum/etc
            from `<target>` file. Inline tags include as is except `@see`/`@link`
            which will be replaced to FQCN (if possible). Other tags are ignored.
            DESC;
    }

    #[Override]
    public static function getTargetDescription(): ?string {
        return 'File path.';
    }

    #[Override]
    public static function getParameters(): string {
        return Parameters::class;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function getParametersDescription(): array {
        return [
            'summary'     => 'Include the class summary? (default `true`)',
            'description' => 'Include the class description? (default `true`)',
        ];
    }

    #[Override]
    public function process(string $path, string $target, Serializable $parameters): string {
        // File?
        $file    = Path::getPath(dirname($path), $target);
        $content = file_get_contents($file);

        if ($content === false) {
            throw new TargetIsNotFile($path, $target);
        }

        // Class?
        [$class, $context] = ((array) $this->getClass($content, $path, $target) + [null, null]);

        if (!$class || !$context) {
            return '';
        }

        // DocBlock?
        $node = $this->getDocNode($class);

        if (!$node) {
            return '';
        }

        // Parse
        $eol    = "\n";
        $text   = $this->getDocText($node);
        $result = '';

        if ($parameters->summary) {
            $summary = trim(implode($eol.$eol, array_slice($text, 0, 1)));

            if ($summary) {
                $result .= $summary.$eol.$eol;
            }
        }

        if ($parameters->description) {
            $description = trim(implode($eol.$eol, array_slice($text, 1)));

            if ($description) {
                $result .= $description.$eol.$eol;
            }
        }

        if ($result) {
            $result = trim($this->preprocess($context, $result)).$eol;
        }

        // Return
        return $result;
    }

    /**
     * @return array{ClassLike, NameContext}|null
     */
    private function getClass(string $content, string $path, string $target): ?array {
        try {
            $class     = null;
            $resolver  = new NameResolver();
            $traverser = new NodeTraverser($resolver);
            $parser    = (new ParserFactory())->createForNewestSupportedVersion();
            $stmts     = (array) $parser->parse($content);
            $stmts     = $traverser->traverse($stmts);
            $context   = $resolver->getNameContext();
            $finder    = new NodeFinder();
            $class     = $finder->findFirst($stmts, static function (Node $node): bool {
                return $node instanceof ClassLike;
            });
        } catch (Exception $exception) {
            throw new TargetIsNotValidPhpFile($path, $target, $exception);
        }

        return $class instanceof ClassLike
            ? [$class, $context]
            : null;
    }

    private function getDocNode(ClassLike $class): ?PhpDocBlockNode {
        // Comment?
        $comment = $class->getDocComment();

        if (!$comment || trim($comment->getText()) === '') {
            return null;
        }

        // Parse
        $lexer  = new Lexer();
        $parser = new PhpDocParser(new TypeParser(new ConstExprParser()), new ConstExprParser());
        $tokens = new TokenIterator($lexer->tokenize($comment->getText()));
        $node   = $parser->parse($tokens);

        // Return
        return $node;
    }

    /**
     * @return list<string>
     */
    private function getDocText(PhpDocBlockNode $node): array {
        $nodes = [];

        foreach ($node->children as $child) {
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

    private function preprocess(NameContext $context, string $string): string {
        return (string) preg_replace_callback(
            pattern : '/\{@(?:see|link)\s+(?P<class>[^}\s\/:]+)(?:::(?P<method>[^(]+\(\)))?\s?\}/imu',
            callback: static function (array $matches) use ($context): string {
                $class  = $context->getResolvedClassName(new Name($matches['class']))->name;
                $method = $matches['method'] ?? null;
                $result = $method
                    ? "`{$class}::{$method}`"
                    : "`{$class}`";

                return $result;
            },
            subject : $string,
            flags   : PREG_UNMATCHED_AS_NULL,
        );
    }
}
