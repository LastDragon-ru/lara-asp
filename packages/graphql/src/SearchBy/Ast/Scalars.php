<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Ast;

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

use function array_merge;
use function array_push;
use function array_unique;
use function array_values;
use function is_array;

class Scalars {
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
     * @param array<string, array<class-string<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator>>> $scalars
     */
    public function __construct(array $scalars = []) {
        $this->scalars = array_merge($this->scalars, $scalars);
    }

    public function isScalar(string $type): bool {
        return isset($this->scalars[$type]);
    }

    /**
     * @return array<class-string<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator>>
     */
    public function getScalarOperators(string $scalar, bool $nullable): array {
        // Base
        $operators = $scalar;

        do {
            $operators = $this->scalars[$operators] ?? [];
        } while (!is_array($operators));

        // Add `null` for nullable
        if ($nullable) {
            array_push($operators, ...$this->getScalarOperators(Directive::ScalarNull, false));
        }

        // Cleanup
        $operators = array_values(array_unique($operators));

        // Return
        return $operators;
    }

    /**
     * @return array<class-string<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator>>
     */
    public function getEnumOperators(string $enum, bool $nullable): array {
        return $this->isScalar($enum)
            ? $this->getScalarOperators($enum, $nullable)
            : $this->getScalarOperators(Directive::ScalarEnum, $nullable);
    }
}
