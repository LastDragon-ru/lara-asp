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

    #[Override]
    public static function getClass(): string {
        return ClassComment::class;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function getExtensions(): array {
        return ['php'];
    }

    #[Override]
    public function resolve(File $file, string $metadata): ?object {
        $class   = $file->as(ClassObject::class);
        $comment = new PhpDoc($class->class->getDocComment()?->getText());
        $comment = new ClassComment($class->class, $class->context, $comment);

        return $comment;
    }

    #[Override]
    public function serialize(File $file, object $value): ?string {
        return null;
    }
}
