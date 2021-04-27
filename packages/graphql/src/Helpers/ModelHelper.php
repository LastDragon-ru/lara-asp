<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use LogicException;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;

use function is_a;
use function sprintf;

class ModelHelper {
    protected bool  $builder;
    protected Model $model;

    public function __construct(Builder|Model $model) {
        $this->builder = $model instanceof Builder;
        $this->model   = $model instanceof Builder
            ? $model->getModel()
            : $model;
    }

    public function getRelation(string $name): Relation {
        $relation = null;

        try {
            $class = new ReflectionClass($this->model);
            $type  = $class->getMethod($name)->getReturnType();

            if ($type instanceof ReflectionNamedType && is_a($type->getName(), Relation::class, true)) {
                if ($this->builder) {
                    $relation = Relation::noConstraints(function () use ($name) {
                        return $this->model->newModelInstance()->{$name}();
                    });
                } else {
                    $relation = $this->model->{$name}();
                }
            }
        } catch (ReflectionException) {
            $relation = null;
        }

        if (!($relation instanceof Relation)) {
            throw new LogicException(sprintf(
                'Property `%s` is not a relation.',
                $name,
            ));
        }

        return $relation;
    }
}
