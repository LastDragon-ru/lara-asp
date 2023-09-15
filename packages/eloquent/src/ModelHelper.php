<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use LastDragon_ru\LaraASP\Eloquent\Exceptions\PropertyIsNotRelation;
use ReflectionClass;
use ReflectionException;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

use function class_uses_recursive;
use function in_array;
use function is_a;
use function is_string;

/**
 * @template TModel of Model
 */
class ModelHelper {
    /**
     * @var array<class-string<TModel>, array<string, bool>>
     */
    private static array $relations = [];

    /**
     * @var array<class-string<TModel>, bool>
     */
    private static array $softDeletable = [];

    private bool $builder;

    /**
     * @var TModel
     */
    private Model $model;

    /**
     * @param Builder<TModel>|TModel|class-string<TModel> $model
     */
    public function __construct(Builder|Model|string $model) {
        if ($model instanceof Builder) {
            $this->builder = true;
            $this->model   = $model->getModel();
        } elseif (is_string($model)) {
            $this->builder = true;
            $this->model   = new $model();
        } else {
            $this->builder = false;
            $this->model   = $model;
        }
    }

    protected function isBuilder(): bool {
        return $this->builder;
    }

    /**
     * @return TModel
     */
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
                self::$relations[$model][$name] = $this->isRelationInstanceOf($type);
            } catch (ReflectionException) {
                // empty
            }
        }

        return self::$relations[$model][$name];
    }

    private function isRelationInstanceOf(?ReflectionType $type): bool {
        $isInstanceOf = false;

        if ($type instanceof ReflectionNamedType) {
            $isInstanceOf = is_a($type->getName(), Relation::class, true);
        } elseif ($type instanceof ReflectionUnionType) {
            $isInstanceOf = true;

            foreach ($type->getTypes() as $t) {
                if (!$this->isRelationInstanceOf($t)) {
                    $isInstanceOf = false;
                    break;
                }
            }
        } elseif ($type instanceof ReflectionIntersectionType) {
            foreach ($type->getTypes() as $t) {
                if ($this->isRelationInstanceOf($t)) {
                    $isInstanceOf = true;
                    break;
                }
            }
        } else {
            // empty
        }

        return $isInstanceOf;
    }

    /**
     * @return Relation<TModel>
     */
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
