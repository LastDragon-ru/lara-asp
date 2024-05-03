<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder;

use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Ignored;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scope;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Directives\ExtendOperatorsDirective;
use LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator;
use WeakMap;

use function array_filter;
use function array_map;
use function array_merge;
use function array_values;
use function is_a;
use function is_object;
use function is_string;

abstract class Operators {
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
     * @var WeakMap<AstManipulator, array<string, list<class-string<Operator>|Operator>>>
     */
    private WeakMap $types;

    /**
     * @var WeakMap<AstManipulator, array<class-string<Operator>, bool>>
     */
    private WeakMap $enabled;

    /**
     * @param array<string, list<class-string<Operator>|string>> $operators
     */
    public function __construct(
        protected readonly ContainerResolver $container,
        array $operators = [],
    ) {
        $this->types   = new WeakMap();
        $this->enabled = new WeakMap();

        foreach ($operators as $type => $value) {
            $this->operators[$type] = $value;
        }
    }

    /**
     * @return class-string<Scope>
     */
    abstract protected function getScope(): string;

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
            $operator = $this->container->getInstance()->make($operator);
        }

        if (!$this->isEnabled($manipulator, $operator)) {
            return null;
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
        return array_values(
            array_filter(
                array_map(
                    function (mixed $operator) use ($manipulator, $source, $context): ?Operator {
                        return $this->getOperator($manipulator, $operator, $source, $context);
                    },
                    $this->getTypeOperators($manipulator, $type),
                ),
            ),
        );
    }

    /**
     * @return list<class-string<Operator>|Operator>
     */
    protected function getTypeOperators(AstManipulator $manipulator, string $type): array {
        // Cached?
        if (isset($this->types[$manipulator][$type])) {
            return $this->types[$manipulator][$type];
        }

        // Prepare
        $this->types[$manipulator]        ??= [];
        $this->types[$manipulator][$type] ??= [];

        // Operators
        $unique    = [];
        $operators = $this->findOperators($manipulator, $type);

        foreach ($operators as $operator) {
            $class            = is_object($operator) ? $operator::class : $operator;
            $unique[$class] ??= $operator;
        }

        $unique = array_values($unique);

        // Cache
        $this->types[$manipulator][$type] = $unique;

        // Return
        return $unique;
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
                    $this->findOperators($manipulator, $operator, 0, $processed),
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

            if ($directive instanceof ExtendOperatorsDirective) {
                $operators[] = $directive->getType() ?? $type;
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

    /**
     * @param Operator|class-string<Operator> $operator
     */
    private function isEnabled(AstManipulator $manipulator, Operator|string $operator): bool {
        // Cached?
        $class = is_object($operator) ? $operator::class : $operator;

        if (isset($this->enabled[$manipulator][$class])) {
            return $this->enabled[$manipulator][$class];
        }

        // Prepare
        $this->enabled[$manipulator] ??= [];

        // Check
        $enabled = true;

        foreach ($this->getDisabledOperators($manipulator) as $disabled) {
            if (is_a($operator, is_object($disabled) ? $disabled::class : $disabled, true)) {
                $enabled = false;
                break;
            }
        }

        // Cache
        $this->enabled[$manipulator][$class] = $enabled;

        // Return
        return $enabled;
    }

    /**
     * @return list<class-string<Operator>|Operator>
     */
    protected function getDisabledOperators(AstManipulator $manipulator): array {
        return [];
    }
}
