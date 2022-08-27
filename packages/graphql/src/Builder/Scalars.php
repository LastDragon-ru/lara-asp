<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder;

use GraphQL\Type\Definition\Type;
use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator as BuilderOperator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\ScalarNoOperators;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\ScalarUnknown;

use function array_map;
use function array_merge;
use function array_push;
use function array_unique;
use function array_values;
use function is_array;
use function is_string;

use const SORT_REGULAR;

abstract class Scalars {
    public const ScalarID      = Type::ID;
    public const ScalarInt     = Type::INT;
    public const ScalarFloat   = Type::FLOAT;
    public const ScalarString  = Type::STRING;
    public const ScalarBoolean = Type::BOOLEAN;
    public const ScalarEnum    = 'Enum';
    public const ScalarNull    = 'Null';

    /**
     * Determines default operators available for each scalar type.
     *
     * @var array<string, array<class-string<Operator>>|string>
     */
    protected array $scalars = [];

    /**
     * Determines additional operators available for scalar type.
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

    public function isScalar(string $scalar): bool {
        return isset($this->scalars[$scalar]);
    }

    /**
     * @param array<class-string<Operator>>|string $operators
     */
    public function addScalar(string $scalar, array|string $operators): void {
        if (is_string($operators) && !$this->isScalar($operators)) {
            throw new ScalarUnknown($operators);
        }

        if (is_array($operators) && !$operators) {
            throw new ScalarNoOperators($scalar);
        }

        $this->scalars[$scalar] = $operators;
    }

    /**
     * @return array<Operator>
     */
    public function getScalarOperators(string $scalar, bool $nullable): array {
        // Is Scalar?
        if (!$this->isScalar($scalar)) {
            throw new ScalarUnknown($scalar);
        }

        // Base
        $base      = $scalar;
        $operators = $scalar;

        do {
            $operators = $this->scalars[$operators] ?? [];
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
            $extends   = $this->getScalarOperators($this->extends[$base], $nullable);
            $operators = array_merge($operators, $extends);
        }

        // Add `null` for nullable
        if ($nullable) {
            array_push($operators, ...$this->getScalarOperators(static::ScalarNull, false));
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
        return $this->isScalar($enum)
            ? $this->getScalarOperators($enum, $nullable)
            : $this->getScalarOperators(static::ScalarEnum, $nullable);
    }
}
