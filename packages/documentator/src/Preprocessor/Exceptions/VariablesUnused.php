<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions;

use Throwable;

use function implode;
use function sprintf;

class VariablesUnused extends InstructionFailed {
    /**
     * @param non-empty-list<string> $variables
     */
    public function __construct(
        string $path,
        string $target,
        private readonly array $variables,
        Throwable $previous = null,
    ) {
        parent::__construct(
            $path,
            $target,
            sprintf(
                'Variables `%s` are not used in `%s`.',
                '`'.implode('`, `', $this->variables).'`',
                $path,
            ),
            $previous,
        );
    }

    /**
     * @return non-empty-list<string>
     */
    public function getVariables(): array {
        return $this->variables;
    }
}
