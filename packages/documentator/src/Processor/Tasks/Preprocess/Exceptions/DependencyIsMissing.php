<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters;
use Throwable;

use function sprintf;

class DependencyIsMissing extends InstructionFailed {
    /**
     * @param class-string $class
     */
    public function __construct(
        Context $context,
        Parameters $parameters,
        private readonly string $class,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            $context,
            $parameters,
            sprintf(
                'Dependency `%s` is missed (`%s` line).',
                $this->class,
                $context->node->getStartLine() ?? 'unknown',
            ),
            $previous,
        );
    }

    /**
     * @return class-string
     */
    public function getClass(): string {
        return $this->class;
    }
}
