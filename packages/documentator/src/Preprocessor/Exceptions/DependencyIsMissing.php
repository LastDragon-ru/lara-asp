<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use Throwable;

use function sprintf;

class DependencyIsMissing extends InstructionFailed {
    /**
     * @param class-string $class
     */
    public function __construct(
        Context $context,
        private readonly string $class,
        Throwable $previous = null,
    ) {
        parent::__construct(
            $context,
            sprintf(
                'The dependency `%s` is missed (in `%s`).',
                $this->class,
                $context->file->getRelativePath($context->root),
            ),
            $previous,
        );
    }

    public function getClass(): string {
        return $this->class;
    }
}
