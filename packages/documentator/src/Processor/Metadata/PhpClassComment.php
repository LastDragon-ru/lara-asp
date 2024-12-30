<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Metadata;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Utils\PhpDoc;
use Override;
use PhpParser\NameContext;
use PhpParser\Node\Stmt\ClassLike;

/**
 * @implements Metadata<?object{class: ClassLike, context: NameContext, comment: PhpDoc}>
 */
class PhpClassComment implements Metadata {
    public function __construct() {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function __invoke(File $file): mixed {
        $class   = $file->getMetadata(PhpClass::class);
        $comment = $class !== null
            ? new class ($class->class, $class->context, new PhpDoc($class->class->getDocComment()?->getText())) {
                public function __construct(
                    public readonly ClassLike $class,
                    public readonly NameContext $context,
                    public readonly PhpDoc $comment,
                ) {
                    // empty
                }
            }
            : null;

        return $comment;
    }
}
