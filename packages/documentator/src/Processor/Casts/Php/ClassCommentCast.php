<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts\Php;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Cast;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Utils\PhpDoc;
use Override;

/**
 * @implements Cast<ClassComment>
 */
class ClassCommentCast implements Cast {
    public function __construct() {
        // empty
    }

    #[Override]
    public static function class(): string {
        return ClassComment::class;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function glob(): array|string {
        return '*.php';
    }

    #[Override]
    public function castTo(File $file, string $class): ?object {
        $class   = $file->as(ClassObject::class);
        $comment = new PhpDoc($class->class->getDocComment()?->getText());
        $comment = new ClassComment($class->class, $class->context, $comment);

        return $comment;
    }

    #[Override]
    public function castFrom(File $file, object $value): ?string {
        return null;
    }
}
