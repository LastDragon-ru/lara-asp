<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator as BuilderOperator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Operators as BuilderOperators;
use LastDragon_ru\LaraASP\GraphQL\Package;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
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
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Complex\Relation;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Condition;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical\AllOf;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical\AnyOf;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical\Not;

use function config;

class Operators extends BuilderOperators {
    public const Extra     = 'Extra';
    public const Number    = 'Number';
    public const Enum      = 'Enum';
    public const Condition = 'Condition';

    /**
     * @inheritDoc
     */
    protected array $operators = [
        // Standard types
        Operators::ID        => [
            Equal::class,
            NotEqual::class,
            In::class,
            NotIn::class,
        ],
        Operators::Int       => [
            Operators::Number,
            BitwiseOr::class,
            BitwiseXor::class,
            BitwiseAnd::class,
            BitwiseLeftShift::class,
            BitwiseRightShift::class,
        ],
        Operators::Float     => [
            Operators::Number,
        ],
        Operators::Boolean   => [
            Equal::class,
            NotEqual::class,
        ],
        Operators::String    => [
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
        Operators::Number    => [
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
        Operators::Enum      => [
            Equal::class,
            NotEqual::class,
            In::class,
            NotIn::class,
        ],
        Operators::Null      => [
            IsNull::class,
            IsNotNull::class,
        ],
        Operators::Extra     => [
            AllOf::class,
            AnyOf::class,
            Not::class,
        ],
        Operators::Condition => [
            Relation::class,
            Condition::class,
        ],
    ];

    public function __construct() {
        /** @var array<string,array<class-string<BuilderOperator>|string>> $operators */
        $operators = (array) config(Package::Name.'.search_by.operators');

        parent::__construct($operators);
    }

    public function getScope(): string {
        return Directive::getScope();
    }
}
