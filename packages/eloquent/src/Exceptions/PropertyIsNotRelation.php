<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Exceptions;

use Illuminate\Database\Eloquent\Model;
use LastDragon_ru\LaraASP\Eloquent\PackageException;
use Throwable;

use function sprintf;

class PropertyIsNotRelation extends PackageException {
    public function __construct(
        protected Model $model,
        protected string $property,
        Throwable $previous = null,
    ) {
        parent::__construct(sprintf(
            'Property `%s::%s()` is not a relation.',
            $this->model::class,
            $this->property,
        ), $previous);
    }
}
