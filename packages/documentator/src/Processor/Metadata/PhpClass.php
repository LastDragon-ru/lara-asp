<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Metadata;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Override;
use PhpParser\Error;
use PhpParser\NameContext;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;

/**
 * @implements Metadata<?object{class: ClassLike, context: NameContext}>
 */
class PhpClass implements Metadata {
    public function __construct() {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function __invoke(File $file): mixed {
        $class    = null;
        $content  = $file->getMetadata(Content::class);
        $resolver = new NameResolver();

        try {
            $stmts  = $this->parse($resolver, $content);
            $finder = new NodeFinder();
            $class  = $finder->findFirst($stmts, static function (Node $node): bool {
                return $node instanceof ClassLike;
            });
        } catch (Error) {
            // not a php file
        }

        return $class instanceof ClassLike
            ? new class ($class, $resolver->getNameContext()) {
                public function __construct(
                    public readonly ClassLike $class,
                    public readonly NameContext $context,
                ) {
                    // empty
                }
            }
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
}
