<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeTemplate\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Exceptions\InstructionFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeTemplate\Parameters;
use Throwable;

use function implode;
use function sprintf;

class TemplateVariablesMissed extends InstructionFailed {
    /**
     * @param non-empty-list<string> $variables
     */
    public function __construct(
        Context $context,
        Parameters $parameters,
        private readonly array $variables,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            $context,
            $parameters,
            sprintf(
                'Variables `%s` required in `%s`, but missed (`%s` line).',
                '`'.implode('`, `', $this->variables).'`',
                $parameters->target,
                $context->node->getStartLine() ?? 'unknown',
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
