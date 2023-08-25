<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions;

use Throwable;

use function sprintf;

class DocumentTitleIsMissing extends InstructionFailed {
    public function __construct(
        string $path,
        string $target,
        private readonly string $document,
        Throwable $previous = null,
    ) {
        parent::__construct(
            $path,
            $target,
            sprintf(
                "The `%s` doesn't contain `# Header` (in `%s`).",
                $this->document,
                $path,
            ),
            $previous,
        );
    }

    public function getDocument(): string {
        return $this->document;
    }
}
