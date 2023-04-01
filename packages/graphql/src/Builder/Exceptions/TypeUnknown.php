<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scope;
use Throwable;

use function sprintf;

class TypeUnknown extends BuilderException {
    /**
     * @param class-string<Scope> $scope
     */
    public function __construct(
        protected string $scope,
        protected string $name,
        Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Type `%s` in `%s` scope is not defined.',
                $this->name,
                $this->scope,
            ),
            $previous,
        );
    }

    public function getName(): string {
        return $this->name;
    }
}
