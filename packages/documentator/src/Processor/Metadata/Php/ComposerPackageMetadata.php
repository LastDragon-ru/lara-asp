<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Php;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Composer\ComposerJsonFactory;
use LastDragon_ru\LaraASP\Documentator\Composer\Package;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\MetadataResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\FileSystem\Content;
use Override;

/**
 * @implements MetadataResolver<Package>
 */
readonly class ComposerPackageMetadata implements MetadataResolver {
    public function __construct(
        protected ComposerJsonFactory $factory,
    ) {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function getExtensions(): array {
        return ['json'];
    }

    #[Override]
    public function isSupported(string $metadata): bool {
        return $metadata === Package::class;
    }

    #[Override]
    public function resolve(File $file, string $metadata): mixed {
        return new Package($this->factory->createFromJson($file->as(Content::class)->content));
    }

    #[Override]
    public function serialize(FilePath $path, object $value): ?string {
        return null;
    }
}
