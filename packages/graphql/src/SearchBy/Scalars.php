<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy;

use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator as OperatorContract;
use LastDragon_ru\LaraASP\GraphQL\Package;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\ScalarNoOperators;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\ScalarUnknown;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\Between;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\BitwiseAnd;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\BitwiseLeftShift;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\BitwiseOr;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\BitwiseRightShift;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\BitwiseXor;
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
use function array_merge;
use function array_push;
use function array_unique;
use function array_values;
use function is_array;
use function is_string;

use const SORT_REGULAR;

class Scalars {
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
        Directive::ScalarInt     => [
            BitwiseOr::class,
            BitwiseXor::class,
            BitwiseAnd::class,
            BitwiseLeftShift::class,
            BitwiseRightShift::class,
        ],
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
     * Determines additional operators available for scalar type.
     *
     * @var array<string, string>
     */
    protected array $extends = [
        Directive::ScalarInt   => Directive::ScalarNumber,
        Directive::ScalarFloat => Directive::ScalarNumber,
    ];

    public function __construct(
        private Container $container,
        Repository $config,
    ) {
        /** @var array<string,array<class-string<Operator>>|string> $scalars */
        $scalars = (array) $config->get(Package::Name.'.search_by.scalars');

        foreach ($scalars as $scalar => $operators) {
            $this->addScalar($scalar, $operators);
        }
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
     * @return array<OperatorContract>
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
        $container = $this->getContainer();
        $operators = array_map(static function (string $operator) use ($container): OperatorContract {
            return $container->make($operator);
        }, array_unique($operators));

        // Extends
        if (isset($this->extends[$scalar])) {
            $extends   = $this->getScalarOperators($this->extends[$scalar], $nullable);
            $operators = array_merge($operators, $extends);
        }

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
     * @return array<OperatorContract>
     */
    public function getEnumOperators(string $enum, bool $nullable): array {
        return $this->isScalar($enum)
            ? $this->getScalarOperators($enum, $nullable)
            : $this->getScalarOperators(Directive::ScalarEnum, $nullable);
    }
}
