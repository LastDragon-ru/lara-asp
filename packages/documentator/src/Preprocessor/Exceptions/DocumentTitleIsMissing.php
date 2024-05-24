<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Throwable;

use function sprintf;

class DocumentTitleIsMissing extends InstructionFailed {
    public function __construct(
        Context $context,
        private readonly File $document,
        Throwable $previous = null,
    ) {
        parent::__construct(
            $context,
            sprintf(
                "The `%s` doesn't contain `# Header` (in `%s`).",
                $this->document->getRelativePath($context->file),
                $context->file->getRelativePath($context->root),
            ),
            $previous,
        );
    }

    public function getDocument(): File {
        return $this->document;
    }
}
