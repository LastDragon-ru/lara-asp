<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Php;

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

    #[Override]
    public static function getClass(): string {
        return Package::class;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function getExtensions(): array {
        return ['json'];
    }

    #[Override]
    public function resolve(File $file, string $metadata): ?object {
        return new Package($this->factory->createFromJson($file->as(Content::class)->content));
    }

    #[Override]
    public function serialize(File $file, object $value): ?string {
        return null;
    }
}
