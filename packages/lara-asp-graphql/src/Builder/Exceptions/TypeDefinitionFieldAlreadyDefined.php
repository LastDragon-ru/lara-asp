<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions;

use Throwable;

use function sprintf;

class TypeDefinitionFieldAlreadyDefined extends BuilderException {
    public function __construct(
        protected string $name,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Field `%s` already defined.',
                $this->name,
            ),
            $previous,
        );
    }

    public function getName(): string {
        return $this->name;
    }
}
