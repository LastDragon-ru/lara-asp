<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference\Node as ReferenceNode;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;

readonly class Context {
    public function __construct(
        public File $file,
        public Document $document,
        public ReferenceNode $node,
    ) {
        // empty
    }
}
