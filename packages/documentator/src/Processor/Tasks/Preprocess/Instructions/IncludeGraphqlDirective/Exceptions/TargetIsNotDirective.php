<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeGraphqlDirective\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Exceptions\InstructionFailed;
use Throwable;

use function sprintf;

class TargetIsNotDirective extends InstructionFailed {
    public function __construct(Context $context, ?Throwable $previous = null) {
        parent::__construct(
            $context,
            sprintf(
                'The `%s` is not a directive (in `%s`).',
                $context->target,
                $context->root->getRelativePath($context->file),
            ),
            $previous,
        );
    }
}
