<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder;

use GraphQL\Type\Definition\Type;
use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\TypeUnknown;

use function array_map;
use function array_merge;
use function array_pop;
use function array_push;
use function array_unique;
use function array_values;
use function is_a;
use function sort;

use const SORT_REGULAR;

abstract class Operators {
    public const ID      = Type::ID;
    public const Int     = Type::INT;
    public const Float   = Type::FLOAT;
    public const String  = Type::STRING;
    public const Boolean = Type::BOOLEAN;
    public const Null    = 'Null';

    /**
     * Determines default operators available for each type.
     *
     * @var array<string, array<class-string<Operator>|string>>
     */
    protected array $operators = [];

    public function __construct() {
        // empty
    }

    abstract public function getScope(): string;

    /**
     * @template T of Operator
     *
     * @param class-string<T> $operator
     *
     * @return T
     */
    public function getOperator(string $operator): Operator {
        return Container::getInstance()->make($operator);
    }

    public function hasOperators(string $type): bool {
        return isset($this->operators[$type]) && !!$this->operators[$type];
    }

    /**
     * @param array<class-string<Operator>|string> $operators
     */
    public function setOperators(string $type, array $operators): void {
        $this->operators[$type] = $operators;
    }

    /**
     * @return array<Operator>
     */
    public function getOperators(string $type, bool $nullable): array {
        // Is known?
        if (!$this->hasOperators($type)) {
            throw new TypeUnknown($this->getScope(), $type);
        }

        // Base
        $extends   = [$type];
        $operators = [];

        do {
            $operator = array_pop($extends);

            if (!is_a($operator, Operator::class, true)) {
                $extends = array_merge($extends, $this->operators[$operator] ?? []);
            } else {
                $operators[] = $operator;
            }
        } while ($extends);

        sort($operators);

        // Create Instances
        $operators = array_map(function (string $operator): Operator {
            return $this->getOperator($operator);
        }, array_unique($operators));

        // Add `null` for nullable
        if ($nullable && $this->hasOperators(static::Null)) {
            array_push($operators, ...$this->getOperators(static::Null, false));
        }

        // Cleanup
        $operators = array_values(array_unique($operators, SORT_REGULAR));

        // Return
        return $operators;
    }
}
