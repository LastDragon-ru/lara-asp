<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use Throwable;

use function sprintf;

class TemplateDataMissed extends InstructionFailed {
    public function __construct(Context $context, Throwable $previous = null) {
        parent::__construct(
            $context,
            sprintf(
                'The `data` is required for `%s`.',
                $context->file->getRelativePath($context->root),
            ),
            $previous,
        );
    }
}
