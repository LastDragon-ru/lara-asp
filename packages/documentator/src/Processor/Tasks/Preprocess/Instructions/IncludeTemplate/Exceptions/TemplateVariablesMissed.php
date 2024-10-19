<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeTemplate\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Exceptions\InstructionFailed;
use Throwable;

use function implode;
use function sprintf;

class TemplateVariablesMissed extends InstructionFailed {
    /**
     * @param non-empty-list<string> $variables
     */
    public function __construct(
        Context $context,
        private readonly array $variables,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            $context,
            sprintf(
                'Variables `%s` required in `%s`, but missed.',
                '`'.implode('`, `', $this->variables).'`',
                $context->root->getRelativePath($context->file),
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
