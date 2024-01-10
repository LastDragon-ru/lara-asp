<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions;

use Exception;
use LastDragon_ru\LaraASP\Documentator\PackageViewer;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\ParameterizableInstruction;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TargetIsNotFile;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TargetIsNotValidPhpFile;
use LastDragon_ru\LaraASP\Documentator\Utils\Path;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;
use Override;
use phpDocumentor\Reflection\DocBlockFactory;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;

use function dirname;
use function file_get_contents;
use function trim;

/**
 * @implements ParameterizableInstruction<IncludeDocBlockParameters>
 */
class IncludeDocBlock implements ParameterizableInstruction {
    public function __construct(
        protected readonly PackageViewer $viewer,
    ) {
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
            from `<target>` file. Inline tags include as is. Other tags are
            ignored.
            DESC;
    }

    #[Override]
    public static function getTargetDescription(): ?string {
        return 'File path.';
    }

    #[Override]
    public static function getParameters(): string {
        return IncludeDocBlockParameters::class;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function getParametersDescription(): array {
        return [
            'summary'     => 'Include the class summary? (default `false`)',
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
        try {
            $class  = null;
            $parser = (new ParserFactory())->createForNewestSupportedVersion();
            $stmts  = (array) $parser->parse($content);
            $finder = new NodeFinder();
            $class  = $finder->findFirst($stmts, static function (Node $node): bool {
                return $node instanceof ClassLike;
            });
        } catch (Exception $exception) {
            throw new TargetIsNotValidPhpFile($path, $target, $exception);
        }

        if (!($class instanceof ClassLike)) {
            return '';
        }

        // DocBlock?
        $comment = $class->getDocComment()?->getText();

        if (!$comment) {
            return '';
        }

        // Parse
        $eol      = "\n";
        $result   = '';
        $factory  = DocBlockFactory::createInstance();
        $docblock = $factory->create($comment);

        if ($parameters->summary) {
            $summary = trim($docblock->getSummary());

            if ($summary) {
                $result .= $summary.$eol.$eol;
            }
        }

        if ($parameters->description) {
            $description = trim((string) $docblock->getDescription());

            if ($description) {
                $result .= $description.$eol.$eol;
            }
        }

        if ($result) {
            $result = trim($result).$eol;
        }

        // Return
        return $result;
    }
}
