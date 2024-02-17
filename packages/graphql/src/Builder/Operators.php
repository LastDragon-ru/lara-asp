<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder;

use GraphQL\Type\Definition\Type;
use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scope;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\TypeUnknown;

use function array_filter;
use function array_key_exists;
use function array_map;
use function array_merge;
use function array_push;
use function array_shift;
use function array_unique;
use function array_values;
use function is_a;

abstract class Operators {
    public const ID      = Type::ID;
    public const Int     = Type::INT;
    public const Float   = Type::FLOAT;
    public const String  = Type::STRING;
    public const Boolean = Type::BOOLEAN;

    /**
     * Determines default operators available for each type.
     *
     * @var array<string, list<class-string<Operator>|string>>
     */
    protected array $default = [];

    /**
     * Determines actual operators available for each type.
     *
     * @var array<string, list<class-string<Operator>|string>>
     */
    private array $operators = [];

    /**
     * @param array<string, list<class-string<Operator>|string>> $operators
     */
    public function __construct(array $operators = []) {
        foreach ($operators as $type => $value) {
            $this->operators[$type] = $value;
        }
    }

    /**
     * @return class-string<Scope>
     */
    abstract public function getScope(): string;

    public function hasType(string $type): bool {
        return array_key_exists($type, $this->operators)
            || array_key_exists($type, $this->default);
    }

    /**
     * @template T of Operator
     *
     * @param class-string<T> $operator
     *
     * @return T|null
     */
    public function getOperator(string $operator): ?Operator {
        return Container::getInstance()->make($operator);
    }

    /**
     * @return list<Operator>
     */
    public function getOperators(string $type): array {
        // Is known?
        if (!$this->hasType($type)) {
            throw new TypeUnknown($this->getScope(), $type);
        }

        // Operators
        $operators = $this->findOperators($type);
        $operators = array_map($this->getOperator(...), $operators);
        $operators = array_values(array_filter($operators));

        // Return
        return $operators;
    }

    /**
     * @return list<class-string<Operator>>
     */
    private function findOperators(string $type): array {
        $extends   = $this->operators[$type] ?? $this->default[$type] ?? [];
        $operators = [];
        $processed = [];

        do {
            $operator = array_shift($extends);

            if ($operator === null || isset($processed[$operator])) {
                continue;
            }

            if (is_a($operator, Operator::class, true)) {
                $operators[] = $operator;
            } elseif ($type === $operator) {
                array_push($extends, ...($this->default[$operator] ?? []));
            } else {
                $operators = array_merge($operators, $this->findOperators($operator));
            }

            $processed[$operator] = true;
        } while ($extends);

        return array_values(array_unique($operators));
    }
}
