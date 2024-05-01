<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions;

use Throwable;

use function sprintf;

class DependencyIsMissing extends InstructionFailed {
    /**
     * @param class-string $class
     */
    public function __construct(
        string $path,
        string $target,
        private readonly string $class,
        Throwable $previous = null,
    ) {
        parent::__construct(
            $path,
            $target,
            sprintf(
                'The dependency `%s` is missed (in `%s`).',
                $this->class,
                $path,
            ),
            $previous,
        );
    }

    public function getClass(): string {
        return $this->class;
    }
}
