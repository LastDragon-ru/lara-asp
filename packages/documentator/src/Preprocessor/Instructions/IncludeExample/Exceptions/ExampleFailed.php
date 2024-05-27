<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeExample\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\InstructionFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Throwable;

use function sprintf;

class ExampleFailed extends InstructionFailed {
    public function __construct(
        Context $context,
        private readonly File $example,
        Throwable $previous = null,
    ) {
        parent::__construct(
            $context,
            sprintf(
                'Example `%s` failed (in `%s`).',
                $this->example->getRelativePath($context->root),
                $context->file->getRelativePath($context->root),
            ),
            $previous,
        );
    }

    public function getExample(): File {
        return $this->example;
    }
}
