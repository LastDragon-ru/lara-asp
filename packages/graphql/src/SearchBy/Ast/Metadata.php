<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Ast;

use Illuminate\Contracts\Container\Container;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\ComplexOperator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\TypeDefinition;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\TypeDefinitionProvider;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\ClassIsNotComplexOperator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\ClassIsNotDefinition;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\ClassIsNotOperator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\DefinitionAlreadyDefined;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\DefinitionUnknown;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\ScalarNoOperators;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\ScalarUnknown;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\Between;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\Contains;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\EndsWith;
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
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\StartsWith;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical\AllOf;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical\AnyOf;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical\Not;

use function array_map;
use function array_push;
use function array_unique;
use function array_values;
use function is_a;
use function is_array;
use function is_string;

use const SORT_REGULAR;

class Metadata {
    /**
     * Determines default operators available for each scalar type.
     *
     * @var array<string, array<class-string<Operator>>|string>
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
            Contains::class,
            StartsWith::class,
            EndsWith::class,
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
     * @var array<string,Operator>
     */
    protected array $operators = [];

    /**
     * Cached complex operators instances.
     *
     * @var array<string,ComplexOperator>
     */
    protected array $complex = [];

    /**
     * Types definitions.
     *
     * @var array<string,TypeDefinition>
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
    ) {
        // empty
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
     * @return array<Operator>
     */
    public function getEnumOperators(string $enum, bool $nullable): array {
        return $this->isScalar($enum)
            ? $this->getScalarOperators($enum, $nullable)
            : $this->getScalarOperators(Directive::ScalarEnum, $nullable);
    }

    /**
     * @param class-string<Operator> $class
     */
    public function getOperatorInstance(string $class): Operator {
        if (!isset($this->operators[$class])) {
            // Is operator?
            if (!is_a($class, Operator::class, true)) {
                throw new ClassIsNotOperator($class);
            }

            // Create Instance
            $operator = $this->container->make($class);

            if ($operator instanceof TypeDefinitionProvider) {
                $this->addDefinitions($operator);
            }

            // Save
            $this->operators[$class] = $operator;
        }

        return $this->operators[$class];
    }

    /**
     * @param class-string<ComplexOperator> $class
     */
    public function getComplexOperatorInstance(string $class): ComplexOperator {
        if (!isset($this->complex[$class])) {
            // Is operator?
            if (!is_a($class, ComplexOperator::class, true)) {
                throw new ClassIsNotComplexOperator($class);
            }

            // Create Instance
            $operator = $this->container->make($class);

            if ($operator instanceof TypeDefinitionProvider) {
                $this->addDefinitions($operator);
            }

            // Save
            $this->complex[$class] = $operator;
        }

        return $this->complex[$class];
    }

    public function addDefinitions(TypeDefinitionProvider $provider): void {
        foreach ($provider->getDefinitions() as $name => $definition) {
            $this->addDefinition($name, $definition);
        }
    }

    /**
     * @param class-string<TypeDefinition> $definition
     */
    public function addDefinition(string $type, string $definition): void {
        // Defined?
        if (isset($this->definitions[$type]) && $this->definitions[$type]::class !== $definition) {
            throw new DefinitionAlreadyDefined($type);
        }

        // Is Definition?
        if (!is_a($definition, TypeDefinition::class, true)) {
            throw new ClassIsNotDefinition($definition);
        }

        // Add
        $this->definitions[$type] = $this->container->make($definition);
    }

    public function getDefinition(string $type): TypeDefinition {
        if (!isset($this->definitions[$type])) {
            throw new DefinitionUnknown($type);
        }

        return $this->definitions[$type];
    }

    public function getType(string $type): ?string {
        return $this->types[$type] ?? null;
    }

    public function addType(string $type, string $fullyQualifiedName): void {
        $this->types[$type] = $fullyQualifiedName;
    }
}
