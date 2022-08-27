<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy;

use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator as BuilderOperator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Scalars as BuilderScalars;
use LastDragon_ru\LaraASP\GraphQL\Package;
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

class Scalars extends BuilderScalars {
    public const ScalarLogic  = 'Logic';
    public const ScalarNumber = 'Number';

    /**
     * @inheritdoc
     */
    protected array $scalars = [
        // Standard types
        Scalars::ScalarID      => [
            Equal::class,
            NotEqual::class,
            In::class,
            NotIn::class,
        ],
        Scalars::ScalarInt     => [
            BitwiseOr::class,
            BitwiseXor::class,
            BitwiseAnd::class,
            BitwiseLeftShift::class,
            BitwiseRightShift::class,
        ],
        Scalars::ScalarFloat   => Scalars::ScalarNumber,
        Scalars::ScalarBoolean => [
            Equal::class,
        ],
        Scalars::ScalarString  => [
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
        Scalars::ScalarNumber  => [
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
        Scalars::ScalarEnum    => [
            Equal::class,
            NotEqual::class,
            In::class,
            NotIn::class,
        ],
        Scalars::ScalarNull    => [
            IsNull::class,
            IsNotNull::class,
        ],
        Scalars::ScalarLogic   => [
            AllOf::class,
            AnyOf::class,
            Not::class,
        ],
    ];

    /**
     * @inheritdoc
     */
    protected array $extends = [
        Scalars::ScalarInt   => Scalars::ScalarNumber,
        Scalars::ScalarFloat => Scalars::ScalarNumber,
    ];

    public function __construct(
        Container $container,
        Repository $config,
    ) {
        parent::__construct($container);

        /** @var array<string,array<class-string<BuilderOperator>>|string> $scalars */
        $scalars = (array) $config->get(Package::Name.'.search_by.scalars');

        foreach ($scalars as $scalar => $operators) {
            $this->addScalar($scalar, $operators);
        }
    }
}
