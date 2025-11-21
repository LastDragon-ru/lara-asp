<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts\FileSystem;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Adapter;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Cast;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Override;

/**
 * @implements Cast<Content>
 */
readonly class ContentCast implements Cast {
    public function __construct(
        protected Adapter $adapter,
    ) {
        // empty
    }

    #[Override]
    public static function class(): string {
        return Content::class;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function glob(): array|string {
        return '*';
    }

    #[Override]
    public function castTo(File $file, string $class): ?object {
        return new Content($this->adapter->read($file->path));
    }

    #[Override]
    public function castFrom(File $file, object $value): ?string {
        return $value->content;
    }
}
