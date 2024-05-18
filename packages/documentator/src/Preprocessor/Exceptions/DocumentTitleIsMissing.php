<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use Throwable;

use function sprintf;

class DocumentTitleIsMissing extends InstructionFailed {
    public function __construct(
        Context $context,
        private readonly string $document,
        Throwable $previous = null,
    ) {
        parent::__construct(
            $context,
            sprintf(
                "The `%s` doesn't contain `# Header` (in `%s`).",
                $this->document,
                $context->path,
            ),
            $previous,
        );
    }

    public function getDocument(): string {
        return $this->document;
    }
}
