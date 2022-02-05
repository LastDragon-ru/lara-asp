<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use LastDragon_ru\LaraASP\Eloquent\Exceptions\PropertyIsNotRelation;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;

use function class_uses_recursive;
use function in_array;
use function is_a;
use function is_string;

class ModelHelper {
    /**
     * @var array<class-string<Model>, array<string, bool>>
     */
    private static array $relations = [];

    /**
     * @var array<class-string<Model>, bool>
     */
    private static array $softDeletable = [];

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

    public function isRelation(string $name): bool {
        $model = $this->getModel()::class;

        if (!isset(self::$relations[$model][$name])) {
            self::$relations[$model][$name] = false;

            try {
                $class                          = new ReflectionClass($model);
                $type                           = $class->getMethod($name)->getReturnType();
                self::$relations[$model][$name] = $type instanceof ReflectionNamedType
                    && is_a($type->getName(), Relation::class, true);
            } catch (ReflectionException) {
                // empty
            }
        }

        return self::$relations[$model][$name];
    }

    public function getRelation(string $name): Relation {
        $relation = null;
        $model    = $this->getModel();

        if ($this->isRelation($name)) {
            if ($this->isBuilder()) {
                $relation = Relation::noConstraints(static function () use ($model, $name) {
                    return $model->newModelInstance()->{$name}();
                });
            } else {
                $relation = $model->{$name}();
            }
        }

        if (!($relation instanceof Relation)) {
            throw new PropertyIsNotRelation($model, $name);
        }

        return $relation;
    }

    public function isSoftDeletable(): bool {
        $model = $this->getModel()::class;

        if (!isset(self::$softDeletable[$model])) {
            self::$softDeletable[$model] = in_array(SoftDeletes::class, class_uses_recursive($model), true);
        }

        return self::$softDeletable[$model];
    }

    public static function resetCache(): void {
        self::$relations     = [];
        self::$softDeletable = [];
    }
}
