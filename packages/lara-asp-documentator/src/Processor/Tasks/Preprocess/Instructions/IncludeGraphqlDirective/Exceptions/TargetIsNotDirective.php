<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeGraphqlDirective\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Exceptions\InstructionFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeGraphqlDirective\Parameters;
use Throwable;

use function sprintf;

class TargetIsNotDirective extends InstructionFailed {
    public function __construct(Context $context, Parameters $parameters, ?Throwable $previous = null) {
        parent::__construct(
            $context,
            $parameters,
            sprintf(
                'The `%s` is not a directive (`%s` line).',
                $parameters->target,
                $context->node->getStartLine() ?? 'unknown',
            ),
            $previous,
        );
    }
}
