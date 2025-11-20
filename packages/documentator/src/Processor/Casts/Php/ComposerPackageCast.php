<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts\Php;

use LastDragon_ru\LaraASP\Documentator\Composer\ComposerJsonFactory;
use LastDragon_ru\LaraASP\Documentator\Composer\Package;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\FileSystem\Content;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Cast;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Override;

/**
 * @implements Cast<Package>
 */
readonly class ComposerPackageCast implements Cast {
    public function __construct(
        protected ComposerJsonFactory $factory,
    ) {
        // empty
    }

    #[Override]
    public static function getClass(): string {
        return Package::class;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function glob(): array|string {
        return '*.json';
    }

    #[Override]
    public function castTo(File $file, string $class): ?object {
        return new Package($this->factory->createFromJson($file->as(Content::class)->content));
    }

    #[Override]
    public function castFrom(File $file, object $value): ?string {
        return null;
    }
}
