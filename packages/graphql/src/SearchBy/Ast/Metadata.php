<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Ast;

use Illuminate\Contracts\Container\Container;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\ComplexOperator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\TypeDefinition;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\TypeDefinitionProvider;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\Between;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\Equal;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\GreaterThan;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\GreaterThanOrEqual;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\In;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\IsNotNull;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\IsNull;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\LessThan;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\LessThanOrEqual;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\Like;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\NotBetween;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\NotEqual;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\NotIn;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\NotLike;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical\AllOf;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical\AnyOf;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical\Not;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\SearchByException;

use function array_map;
use function array_push;
use function array_unique;
use function array_values;
use function is_a;
use function is_array;
use function is_string;
use function sprintf;

use const SORT_REGULAR;

class Metadata {
    /**
     * Determines default operators available for each scalar type.
     *
     * @var array<string, array<string>|string>
     */
    protected array $scalars = [
        // Standard types
        Directive::ScalarID      => [
            Equal::class,
            NotEqual::class,
            In::class,
            NotIn::class,
        ],
        Directive::ScalarInt     => Directive::ScalarNumber,
        Directive::ScalarFloat   => Directive::ScalarNumber,
        Directive::ScalarBoolean => [
            Equal::class,
        ],
        Directive::ScalarString  => [
            Equal::class,
            NotEqual::class,
            Like::class,
            NotLike::class,
            In::class,
            NotIn::class,
        ],

        // Special types
        Directive::ScalarNumber  => [
            Equal::class,
            NotEqual::class,
            LessThan::class,
            LessThanOrEqual::class,
            GreaterThan::class,
            GreaterThanOrEqual::class,
            In::class,
            NotIn::class,
            Between::class,
            NotBetween::class,
        ],
        Directive::ScalarEnum    => [
            Equal::class,
            NotEqual::class,
            In::class,
            NotIn::class,
        ],
        Directive::ScalarNull    => [
            IsNull::class,
            IsNotNull::class,
        ],
        Directive::ScalarLogic   => [
            AllOf::class,
            AnyOf::class,
            Not::class,
        ],
    ];

    /**
     * Cached operators instances.
     *
     * @var array<string,\LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator>
     */
    protected array $operators = [];

    /**
     * Cached complex operators instances.
     *
     * @var array<string,\LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\ComplexOperator>
     */
    protected array $complex = [];

    /**
     * Types definitions.
     *
     * @var array<string,\LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\TypeDefinition>
     */
    protected array $definitions = [];

    /**
     * Cached types created by definitions.
     *
     * @var array<string,string>
     */
    protected array $types = [];

    public function __construct(
        protected Container $container,
        protected Usage $usage,
    ) {
        // empty
    }

    public function isScalar(string $scalar): bool {
        return isset($this->scalars[$scalar]);
    }

    /**
     * @param array<class-string<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator>>|string $operators
     */
    public function addScalar(string $scalar, array|string $operators): void {
        if (is_string($operators) && !$this->isScalar($operators)) {
            throw new SearchByException(sprintf(
                'Scalar `%s` is not defined.',
                $operators,
            ));
        }

        if (is_array($operators) && empty($operators)) {
            throw new SearchByException(sprintf(
                'Operator list for scalar `%s` cannot be empty.',
                $scalar,
            ));
        }

        $this->scalars[$scalar] = $operators;
    }

    /**
     * @return array<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator>
     */
    public function getScalarOperators(string $scalar, bool $nullable): array {
        // Is Scalar?
        if (!$this->isScalar($scalar)) {
            throw new SearchByException(sprintf(
                'Scalar `%s` is not defined.',
                $scalar,
            ));
        }

        // Base
        $operators = $scalar;

        do {
            $operators = $this->scalars[$operators] ?? [];
        } while (!is_array($operators));

        // Create Instances
        $operators = array_map(function (string $operator): Operator {
            return $this->getOperatorInstance($operator);
        }, $operators);

        // Add `null` for nullable
        if ($nullable) {
            array_push($operators, ...$this->getScalarOperators(Directive::ScalarNull, false));
        }

        // Cleanup
        $operators = array_values(array_unique($operators, SORT_REGULAR));

        // Return
        return $operators;
    }

    /**
     * @return array<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator>
     */
    public function getEnumOperators(string $enum, bool $nullable): array {
        return $this->isScalar($enum)
            ? $this->getScalarOperators($enum, $nullable)
            : $this->getScalarOperators(Directive::ScalarEnum, $nullable);
    }

    /**
     * @param class-string<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator> $class
     */
    public function getOperatorInstance(string $class): Operator {
        if (!isset($this->operators[$class])) {
            // Is operator?
            if (!is_a($class, Operator::class, true)) {
                throw new SearchByException(sprintf(
                    'Operator `%s` must implement `%s`.',
                    $class,
                    Operator::class,
                ));
            }

            // Create Instance
            $operator = $this->container->make($class);

            if ($operator instanceof TypeDefinitionProvider) {
                $this->addDefinitions($operator);
            }

            // Save
            $this->operators[$class] = $operator;
        }

        $this->getUsage()->addValue($class);

        return $this->operators[$class];
    }

    /**
     * @param class-string<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\ComplexOperator> $class
     */
    public function getComplexOperatorInstance(string $class): ComplexOperator {
        if (!isset($this->complex[$class])) {
            // Is operator?
            if (!is_a($class, ComplexOperator::class, true)) {
                throw new SearchByException(sprintf(
                    'Operator `%s` must implement `%s`.',
                    $class,
                    ComplexOperator::class,
                ));
            }

            // Create Instance
            $operator = $this->container->make($class);

            if ($operator instanceof TypeDefinitionProvider) {
                $this->addDefinitions($operator);
            }

            // Save
            $this->complex[$class] = $operator;
        }

        $this->getUsage()->addValue($class);

        return $this->complex[$class];
    }

    public function addDefinitions(TypeDefinitionProvider $provider): void {
        foreach ($provider->getDefinitions() as $name => $definition) {
            $this->addDefinition($name, $definition);
        }
    }

    /**
     * @param class-string<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\TypeDefinition> $definition
     */
    public function addDefinition(string $type, string $definition): void {
        // Defined?
        if (isset($this->definitions[$type]) && $this->definitions[$type]::class !== $definition) {
            throw new SearchByException(sprintf(
                'Definition `%s` already defined.',
                $type,
            ));
        }

        // Is Definition?
        if (!is_a($definition, TypeDefinition::class, true)) {
            throw new SearchByException(sprintf(
                'Definition `%s` must implement `%s`.',
                $definition,
                TypeDefinition::class,
            ));
        }

        // Add
        $this->definitions[$type] = $this->container->make($definition);
    }

    public function getDefinition(string $type): TypeDefinition {
        if (!isset($this->definitions[$type])) {
            throw new SearchByException(sprintf(
                'Definition `%s` is not defined.',
                $type,
            ));
        }

        return $this->definitions[$type];
    }

    public function getType(string $type): ?string {
        return $this->types[$type] ?? null;
    }

    public function addType(string $type, string $fullyQualifiedName): void {
        $this->types[$type] = $fullyQualifiedName;
    }

    /**
     * @return array<class-string<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator|\LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\ComplexOperator>>
     */
    public function getUsedOperators(string $type): array {
        return $this->usages[$type] ?? [];
    }

    /**
     * @return \LastDragon_ru\LaraASP\GraphQL\SearchBy\Ast\Usage<
     *      \LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator|
     *      \LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\ComplexOperator
     *      >
     */
    public function getUsage(): Usage {
        return $this->usage;
    }
}
