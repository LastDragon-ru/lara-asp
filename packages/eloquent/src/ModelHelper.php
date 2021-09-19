<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use LastDragon_ru\LaraASP\Eloquent\Exceptions\PropertyIsNotRelation;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;

use function is_a;
use function is_string;

class ModelHelper {
    private bool  $builder;
    private Model $model;

    /**
     * @param Builder|Model|class-string<Model> $model
     */
    public function __construct(Builder|Model|string $model) {
        if (is_string($model)) {
            $model = new $model();
        }

        $this->builder = $model instanceof Builder;
        $this->model   = $model instanceof Builder
            ? $model->getModel()
            : $model;
    }

    protected function isBuilder(): bool {
        return $this->builder;
    }

    public function getModel(): Model {
        return $this->model;
    }

    public function getRelation(string $name): Relation {
        $relation = null;

        try {
            $model = $this->getModel();
            $class = new ReflectionClass($model);
            $type  = $class->getMethod($name)->getReturnType();

            if ($type instanceof ReflectionNamedType && is_a($type->getName(), Relation::class, true)) {
                if ($this->isBuilder()) {
                    $relation = Relation::noConstraints(static function () use ($model, $name) {
                        return $model->newModelInstance()->{$name}();
                    });
                } else {
                    $relation = $model->{$name}();
                }
            }
        } catch (ReflectionException) {
            $relation = null;
        }

        if (!($relation instanceof Relation)) {
            throw new PropertyIsNotRelation($this->getModel(), $name);
        }

        return $relation;
    }
}
