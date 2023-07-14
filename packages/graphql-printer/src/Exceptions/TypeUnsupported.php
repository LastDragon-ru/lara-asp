<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Exceptions;

use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQLPrinter\PackageException;
use Throwable;

use function sprintf;

/**
 * @deprecated 4.2.1 Please use {@link Unsupported} instead.
 */
class TypeUnsupported extends PackageException {
    public function __construct(
        protected Type $type,
        Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Type `%s` is not (yet) supported.',
                $this->type,
            ),
            $previous,
        );
    }

    public function getType(): Type {
        return $this->type;
    }
}
