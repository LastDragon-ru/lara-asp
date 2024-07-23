<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata;

use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Metadata;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Override;

/**
 * @implements Metadata<?Document>
 */
class Markdown implements Metadata {
    public function __construct() {
        // empty
    }

    #[Override]
    public function __invoke(File $file): mixed {
        return $file->getExtension() === 'md'
            ? new Document($file->getContent(), $file->getPath())
            : null;
    }
}
