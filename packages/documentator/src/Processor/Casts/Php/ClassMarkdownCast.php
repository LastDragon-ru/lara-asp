<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts\Php;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Cast;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Utils\PhpDocumentFactory;
use Override;

/**
 * @implements Cast<Document>
 */
readonly class ClassMarkdownCast implements Cast {
    public function __construct(
        protected PhpDocumentFactory $factory,
    ) {
        // empty
    }

    #[Override]
    public static function class(): string {
        return Document::class;
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
        $comment  = $file->as(ClassComment::class);
        $document = ($this->factory)($comment->comment, $file->path, $comment->context);

        return $document;
    }

    #[Override]
    public function castFrom(File $file, object $value): ?string {
        return null;
    }
}
