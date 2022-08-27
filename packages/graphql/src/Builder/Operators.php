<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder;

use GraphQL\Type\Definition\Type;
use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator as BuilderOperator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\TypeNoOperators;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\TypeUnknown;

use function array_map;
use function array_merge;
use function array_push;
use function array_unique;
use function array_values;
use function is_array;
use function is_string;

use const SORT_REGULAR;

abstract class Operators {
    public const ID      = Type::ID;
    public const Int     = Type::INT;
    public const Float   = Type::FLOAT;
    public const String  = Type::STRING;
    public const Boolean = Type::BOOLEAN;
    public const Enum    = 'Enum';
    public const Null    = 'Null';

    /**
     * Determines default operators available for each type.
     *
     * @var array<string, array<class-string<Operator>>|string>
     */
    protected array $operators = [];

    /**
     * Determines additional operators available for type.
     *
     * @var array<string, string>
     */
    protected array $extends = [];

    public function __construct(
        private Container $container,
    ) {
        // empty
    }

    protected function getContainer(): Container {
        return $this->container;
    }

    public function hasOperators(string $type): bool {
        return isset($this->operators[$type]);
    }

    /**
     * @param array<class-string<Operator>>|string $operators
     */
    public function addOperators(string $type, array|string $operators): void {
        if (is_string($operators) && !$this->hasOperators($operators)) {
            throw new TypeUnknown($operators);
        }

        if (is_array($operators) && !$operators) {
            throw new TypeNoOperators($type);
        }

        $this->operators[$type] = $operators;
    }

    /**
     * @return array<Operator>
     */
    public function getOperators(string $type, bool $nullable): array {
        // Is known?
        if (!$this->hasOperators($type)) {
            throw new TypeUnknown($type);
        }

        // Base
        $base      = $type;
        $operators = $type;

        do {
            $operators = $this->operators[$operators] ?? [];
            $isAlias   = !is_array($operators);

            if ($isAlias) {
                $base = $operators;
            }
        } while ($isAlias);

        // Create Instances
        $container = $this->getContainer();
        $operators = array_map(static function (string $operator) use ($container): Operator {
            return $container->make($operator);
        }, array_unique($operators));

        // Extends
        if (isset($this->extends[$base])) {
            $extends   = $this->getOperators($this->extends[$base], $nullable);
            $operators = array_merge($operators, $extends);
        }

        // Add `null` for nullable
        if ($nullable) {
            array_push($operators, ...$this->getOperators(static::Null, false));
        }

        // Cleanup
        $operators = array_values(array_unique($operators, SORT_REGULAR));

        // Return
        return $operators;
    }

    /**
     * @return array<BuilderOperator>
     */
    public function getEnumOperators(string $enum, bool $nullable): array {
        return $this->hasOperators($enum)
            ? $this->getOperators($enum, $nullable)
            : $this->getOperators(static::Enum, $nullable);
    }
}
