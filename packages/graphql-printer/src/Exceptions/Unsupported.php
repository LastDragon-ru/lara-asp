<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Exceptions;

use LastDragon_ru\LaraASP\GraphQLPrinter\PackageException;
use Throwable;

use function sprintf;

class Unsupported extends PackageException {
    public function __construct(
        protected object $definition,
        Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'The `%s` is not (yet) supported.',
                $this->definition::class,
            ),
            $previous,
        );
    }

    public function getDefinition(): object {
        return $this->definition;
    }
}
