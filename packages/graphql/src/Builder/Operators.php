<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder;

use GraphQL\Type\Definition\Type;
use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Ignored;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scope;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Directives\OperatorsDirective;
use LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator;

use function array_merge;
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

    /**
     * @template T of Operator
     *
     * @param T|class-string<T> $operator
     *
     * @return T|null
     */
    public function getOperator(
        Manipulator $manipulator,
        Operator|string $operator,
        TypeSource $source,
        Context $context,
    ): ?Operator {
        if (!is_a($operator, $this->getScope(), true)) {
            return null;
        }

        if (is_string($operator)) {
            $operator = Container::getInstance()->make($operator);
        }

        if (!$operator->isAvailable($manipulator, $source, $context)) {
            return null;
        }

        return $operator;
    }

    /**
     * @return list<Operator>
     */
    public function getOperators(Manipulator $manipulator, string $type, TypeSource $source, Context $context): array {
        // Operators
        $unique    = [];
        $operators = $this->findOperators($manipulator, $type);

        foreach ($operators as $operator) {
            $operator = $this->getOperator($manipulator, $operator, $source, $context);

            if ($operator && !isset($unique[$operator::class])) {
                $unique[$operator::class] = $operator;
            }
        }

        // Return
        return array_values($unique);
    }

    /**
     * @param array<string, true> $processed
     *
     * @return array<array-key, class-string<Operator>|Operator>
     */
    private function findOperators(
        AstManipulator $manipulator,
        string $type,
        int $level = 0,
        array &$processed = [],
    ): array {
        // We have several levels where operators can be defined - AST, config,
        // and built-in defaults. We are always starting at the highest level
        // and go deeper if there are no operators or if the type with the same
        // name is found.

        // Processed?
        if (isset($processed[$type])) {
            return [];
        }

        // Search for operators
        $list = match ($level) {
            0       => $this->findAstOperators($manipulator, $type),
            1       => $this->findConfigOperators($type),
            2       => $this->findDefaultOperators($type),
            default => null,
        };

        if ($list === null) {
            return [];
        }

        // Merge
        $operators = [];

        foreach ($list as $operator) {
            if (is_a($operator, Operator::class, true)) {
                $operators[] = $operator;
            } elseif ($type !== $operator) {
                $processed[$type] = true;
                $operators        = array_merge(
                    $operators,
                    $this->findOperators($manipulator, $operator, $level, $processed),
                );
            } else {
                $operators = array_merge(
                    $operators,
                    $this->findOperators($manipulator, $operator, $level + 1, $processed),
                );
            }
        }

        // Empty?
        if (!$operators) {
            $operators = $this->findOperators($manipulator, $type, $level + 1, $processed);
        }

        // Mark
        $processed[$type] = true;

        // Return
        return $operators;
    }

    /**
     * @return array<array-key, class-string<Operator>|Operator|string>|null
     */
    private function findAstOperators(AstManipulator $manipulator, string $type): ?array {
        if (!$manipulator->isTypeDefinitionExists($type)) {
            return [];
        }

        $scope      = $this->getScope();
        $operators  = [];
        $directives = $manipulator->getDirectives($manipulator->getTypeDefinition($type));

        foreach ($directives as $directive) {
            if (!($directive instanceof $scope)) {
                continue;
            }

            if ($directive instanceof OperatorsDirective) {
                $operators[] = $directive->getType();
            } elseif ($directive instanceof Operator) {
                $operators[] = $directive;
            } elseif ($directive instanceof Ignored) {
                $operators = null;
                break;
            } else {
                // empty
            }
        }

        return $operators;
    }

    /**
     * @return array<array-key, class-string<Operator>|string>
     */
    private function findConfigOperators(string $type): array {
        return $this->operators[$type] ?? [];
    }

    /**
     * @return array<array-key, class-string<Operator>|string>
     */
    private function findDefaultOperators(string $type): array {
        return $this->default[$type] ?? [];
    }
}
