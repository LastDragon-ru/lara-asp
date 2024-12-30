<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeArtisan\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Exceptions\InstructionFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeArtisan\Parameters;
use Throwable;

use function sprintf;

class ArtisanCommandError extends InstructionFailed {
    public function __construct(
        Context $context,
        Parameters $parameters,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            $context,
            $parameters,
            sprintf(
                'Artisan command `%s` failed (`%s` line).',
                $parameters->target,
                $context->node->getStartLine() ?? 'unknown',
            ),
            $previous,
        );
    }
}
