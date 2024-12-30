<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocBlock\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Exceptions\InstructionFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocBlock\Parameters;
use Throwable;

use function sprintf;

class TargetIsNotValidPhpFile extends InstructionFailed {
    public function __construct(Context $context, Parameters $parameters, ?Throwable $previous = null) {
        parent::__construct(
            $context,
            $parameters,
            sprintf(
                'The `%s` is not a valid PHP file (`%s` line).',
                $parameters->target,
                $context->node->getStartLine() ?? 'unknown',
            ),
            $previous,
        );
    }
}
