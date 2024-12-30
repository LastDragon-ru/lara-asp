<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata;

use LastDragon_ru\LaraASP\Documentator\Composer\ComposerJsonFactory;
use LastDragon_ru\LaraASP\Documentator\Composer\Package;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Metadata;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Override;

/**
 * @implements Metadata<?Package>
 */
readonly class Composer implements Metadata {
    public function __construct(
        protected ComposerJsonFactory $factory,
    ) {
        // empty
    }

    #[Override]
    public function __invoke(File $file): mixed {
        return $file->getExtension() === 'json'
            ? new Package($this->factory->createFromJson($file->getMetadata(Content::class)))
            : null;
    }
}
