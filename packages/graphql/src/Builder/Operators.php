<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder;

use GraphQL\Type\Definition\Type;
use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Ignored;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scope;
use LastDragon_ru\LaraASP\GraphQL\Builder\Directives\OperatorsDirective;
use LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator;

use function array_key_exists;
use function array_merge;
use function array_push;
use function array_shift;
use function array_values;
use function is_a;
use function is_string;

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
     * @param T|class-string<T> $operator
     *
     * @return T|null
     */
    public function getOperator(Operator|string $operator): ?Operator {
        if (!is_a($operator, $this->getScope(), true)) {
            return null;
        }

        if (is_string($operator)) {
            $operator = Container::getInstance()->make($operator);
        }

        return $operator;
    }

    /**
     * @return list<Operator>
     */
    public function getOperators(AstManipulator $manipulator, string $type): array {
        // Operators
        $unique    = [];
        $operators = $this->findOperators($manipulator, $type);

        foreach ($operators as $operator) {
            $operator = $this->getOperator($operator);

            if ($operator && !isset($unique[$operator::class])) {
                $unique[$operator::class] = $operator;
            }
        }

        // Return
        return array_values($unique);
    }

    /**
     * @return array<array-key, class-string<Operator>|Operator>
     */
    private function findOperators(AstManipulator $manipulator, string $type): array {
        // AST have a bigger priority
        $operators = $this->findAstOperators($manipulator, $type);

        if ($operators === null) {
            return [];
        }

        // If no operators in AST, use config
        if (!$operators) {
            array_push($operators, ...$this->findConfigOperators($type));
        }

        // Return
        return $operators;
    }

    /**
     * @return array<array-key, class-string<Operator>|Operator>|null
     */
    private function findAstOperators(AstManipulator $manipulator, string $type): ?array {
        $operators = [];

        if ($manipulator->isTypeDefinitionExists($type)) {
            $node       = $manipulator->getTypeDefinition($type);
            $scope      = $this->getScope();
            $directives = $manipulator->getDirectives($node);

            foreach ($directives as $directive) {
                if (!($directive instanceof $scope)) {
                    continue;
                }

                if ($directive instanceof OperatorsDirective) {
                    $directiveType = $directive->getType();

                    if ($type !== $directiveType) {
                        array_push($operators, ...$this->findOperators($manipulator, $directiveType));
                    } else {
                        array_push($operators, ...$this->findConfigOperators($type));
                    }
                } elseif ($directive instanceof Operator) {
                    $operator = $this->getOperator($directive);

                    if ($operator) {
                        $operators[] = $operator;
                    }
                } elseif ($directive instanceof Ignored) {
                    $operators = null;
                    break;
                } else {
                    // empty
                }
            }
        }

        return $operators;
    }

    /**
     * @return array<array-key, class-string<Operator>>
     */
    private function findConfigOperators(string $type): array {
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
                $operators = array_merge($operators, $this->findConfigOperators($operator));
            }

            $processed[$operator] = true;
        } while ($extends);

        return $operators;
    }
}
