<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeDocBlock;

use Exception;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Instruction as InstructionContract;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeDocBlock\Exceptions\TargetIsNotValidPhpFile;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Resolvers\FileResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Utils\PhpDoc;
use Override;
use PhpParser\NameContext;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;

use function preg_replace_callback;
use function trim;

use const PREG_UNMATCHED_AS_NULL;

/**
 * Includes the docblock of the first PHP class/interface/trait/enum/etc
 * from `<target>` file. Inline tags include as is except `@see`/`@link`
 * which will be replaced to FQCN (if possible). Other tags are ignored.
 *
 * @implements InstructionContract<File, Parameters>
 */
class Instruction implements InstructionContract {
    public function __construct() {
        // empty
    }

    #[Override]
    public static function getName(): string {
        return 'include:docblock';
    }

    #[Override]
    public static function getResolver(): string {
        return FileResolver::class;
    }

    #[Override]
    public static function getParameters(): ?string {
        return Parameters::class;
    }

    #[Override]
    public function __invoke(Context $context, mixed $target, mixed $parameters): string {
        // Class?
        [$class, $context] = ((array) $this->getClass($context, $target->getContent()) + [null, null]);

        if (!$class || !$context) {
            return '';
        }

        // Parse
        $eol    = "\n";
        $doc    = new PhpDoc($class->getDocComment()?->getText(), $eol.$eol);
        $result = '';

        if ($parameters->summary && $parameters->description) {
            $result .= trim($doc->getText());
        } elseif ($parameters->summary) {
            $result .= trim($doc->getSummary());
        } elseif ($parameters->description) {
            $result .= trim($doc->getDescription());
        } else {
            // empty
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
    private function getClass(Context $context, string $content): ?array {
        try {
            $class    = null;
            $resolver = new NameResolver();
            $stmts    = $this->parse($resolver, $content);
            $finder   = new NodeFinder();
            $class    = $finder->findFirst($stmts, static function (Node $node): bool {
                return $node instanceof ClassLike;
            });
        } catch (Exception $exception) {
            throw new TargetIsNotValidPhpFile($context, $exception);
        }

        return $class instanceof ClassLike
            ? [$class, $resolver->getNameContext()]
            : null;
    }

    /**
     * @return array<array-key, Node>
     */
    private function parse(NameResolver $resolver, string $content): array {
        $traverser = new NodeTraverser();
        $traverser->addVisitor($resolver);

        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $stmts  = (array) $parser->parse($content);
        $stmts  = $traverser->traverse($stmts);

        return $stmts;
    }

    private function preprocess(NameContext $context, string $string): string {
        return (string) preg_replace_callback(
            pattern : '/\{@(?:see|link)\s+(?P<class>[^}\s\/:]+)(?:::(?P<method>[^(]+\(\)))?\s?\}/imu',
            callback: static function (array $matches) use ($context): string {
                $class  = (string) $context->getResolvedClassName(new Name($matches['class']));
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
