<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeDocBlock;

use Exception;
use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\ParameterizableInstruction;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TargetIsNotFile;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TargetIsNotValidPhpFile;
use LastDragon_ru\LaraASP\Documentator\Utils\PhpDoc;
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

use function dirname;
use function file_get_contents;
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
    private function getClass(string $content, string $path, string $target): ?array {
        try {
            $class    = null;
            $resolver = new NameResolver();
            $stmts    = $this->parse($resolver, $content);
            $context  = $resolver->getNameContext();
            $finder   = new NodeFinder();
            $class    = $finder->findFirst($stmts, static function (Node $node): bool {
                return $node instanceof ClassLike;
            });
        } catch (Exception $exception) {
            throw new TargetIsNotValidPhpFile($path, $target, $exception);
        }

        return $class instanceof ClassLike
            ? [$class, $context]
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
