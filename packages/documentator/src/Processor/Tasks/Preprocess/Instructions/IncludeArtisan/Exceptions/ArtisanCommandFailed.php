<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeArtisan\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Exceptions\InstructionFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeArtisan\Parameters;
use Throwable;

use function sprintf;

class ArtisanCommandFailed extends InstructionFailed {
    public function __construct(
        Context $context,
        Parameters $parameters,
        private readonly int $result,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            $context,
            $parameters,
            sprintf(
                'Artisan command `%s` exited with status code `%s` (`%s` line).',
                $parameters->target,
                $this->result,
                $context->node->getStartLine() ?? 'unknown',
            ),
            $previous,
        );
    }

    public function getResult(): int {
        return $this->result;
    }
}
