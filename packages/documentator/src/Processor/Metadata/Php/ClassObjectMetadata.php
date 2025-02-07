<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Php;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\MetadataResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Content;
use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;

/**
 * @implements MetadataResolver<ClassObject>
 */
readonly class ClassObjectMetadata implements MetadataResolver {
    public function __construct() {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function getExtensions(): array {
        return ['php'];
    }

    #[Override]
    public function isSupported(string $metadata): bool {
        return $metadata === ClassObject::class;
    }

    #[Override]
    public function resolve(File $file, string $metadata): mixed {
        $content  = $file->getMetadata(Content::class);
        $resolver = new NameResolver();
        $stmts    = $this->parse($resolver, $content);
        $finder   = new NodeFinder();
        $class    = $finder->findFirstInstanceOf($stmts, ClassLike::class);

        if ($class === null) {
            throw new ClassObjectNotFound();
        }

        return new ClassObject($class, $resolver->getNameContext());
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
