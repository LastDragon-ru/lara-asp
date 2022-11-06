<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder;

use GraphQL\Type\Definition\Type;
use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\TypeNoOperators;
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

class Operators {
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
     * @param array<class-string<Operator>|string> $operators
     */
    public function setOperators(string $type, array $operators): void {
        if (!$operators) {
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
        $container = $this->getContainer();
        $operators = array_map(static function (string $operator) use ($container): Operator {
            return $container->make($operator);
        }, array_unique($operators));

        // Add `null` for nullable
        if ($nullable) {
            array_push($operators, ...$this->getOperators(static::Null, false));
        }

        // Cleanup
        $operators = array_values(array_unique($operators, SORT_REGULAR));

        // Return
        return $operators;
    }
}
