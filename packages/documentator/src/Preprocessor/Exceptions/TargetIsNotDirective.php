<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use Throwable;

use function sprintf;

class TargetIsNotDirective extends InstructionFailed {
    public function __construct(Context $context, Throwable $previous = null) {
        parent::__construct(
            $context,
            sprintf(
                'The `%s` is not a directive (in `%s`).',
                $context->target,
                $context->file->getRelativePath($context->root),
            ),
            $previous,
        );
    }
}
