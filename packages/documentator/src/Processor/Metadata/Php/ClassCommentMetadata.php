<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Php;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\MetadataResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Utils\PhpDoc;
use Override;

/**
 * @implements MetadataResolver<ClassComment>
 */
class ClassCommentMetadata implements MetadataResolver {
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
        return $metadata === ClassComment::class;
    }

    #[Override]
    public function resolve(File $file, string $metadata): mixed {
        $class   = $file->as(ClassObject::class);
        $comment = new PhpDoc($class->class->getDocComment()?->getText());
        $comment = new ClassComment($class->class, $class->context, $comment);

        return $comment;
    }
}
