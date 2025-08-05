<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Exceptions;

use LastDragon_ru\GraphQLPrinter\PackageException;
use Throwable;

use function sprintf;

class DirectiveDefinitionNotFound extends PackageException {
    public function __construct(
        protected string $name,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Definition for directive `%s` not found.',
                $this->name,
            ),
            $previous,
        );
    }

    public function getName(): string {
        return $this->name;
    }
}
