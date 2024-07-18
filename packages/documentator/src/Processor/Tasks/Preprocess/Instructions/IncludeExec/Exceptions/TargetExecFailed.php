<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeExec\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Exceptions\InstructionFailed;
use Throwable;

use function sprintf;

class TargetExecFailed extends InstructionFailed {
    public function __construct(Context $context, Throwable $previous = null) {
        parent::__construct(
            $context,
            sprintf(
                'Failed to execute the `%s` command (in `%s`).',
                $context->target,
                $context->file->getRelativePath($context->root),
            ),
            $previous,
        );
    }
}
