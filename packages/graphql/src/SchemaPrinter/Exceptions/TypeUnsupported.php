<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Exceptions;

use GraphQL\Type\Definition\Type;
use Throwable;

use function sprintf;

class TypeUnsupported extends Exception {
    public function __construct(
        protected Type $type,
        Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Type `%s` is not (yet) supported.',
                $this->type,
            ),
            $previous
        );
    }

    public function getType(): Type {
        return $this->type;
    }
}
