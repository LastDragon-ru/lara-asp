<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator as BuilderOperator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Operators as BuilderOperators;
use LastDragon_ru\LaraASP\GraphQL\Package;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorAllOfDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorAnyOfDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorBetweenDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorBitwiseAndDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorBitwiseLeftShiftDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorBitwiseOrDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorBitwiseRightShiftDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorBitwiseXorDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorConditionDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorContainsDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorEndsWithDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorEqualDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorGreaterThanDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorGreaterThanOrEqualDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorInDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorIsNotNullDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorIsNullDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorLessThanDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorLessThanOrEqualDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorLikeDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorNotBetweenDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorNotDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorNotEqualDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorNotInDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorNotLikeDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorRelationDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorStartsWithDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;

use function config;

class Operators extends BuilderOperators {
    public const Null      = Directive::Name.'Null';
    public const Extra     = Directive::Name.'Extra';
    public const Number    = Directive::Name.'Number';
    public const Enum      = Directive::Name.'Enum';
    public const Condition = Directive::Name.'Condition';

    /**
     * @inheritDoc
     */
    protected array $operators = [
        // Standard types
        self::ID        => [
            SearchByOperatorEqualDirective::class,
            SearchByOperatorNotEqualDirective::class,
            SearchByOperatorInDirective::class,
            SearchByOperatorNotInDirective::class,
        ],
        self::Int       => [
            self::Number,
            SearchByOperatorBitwiseOrDirective::class,
            SearchByOperatorBitwiseXorDirective::class,
            SearchByOperatorBitwiseAndDirective::class,
            SearchByOperatorBitwiseLeftShiftDirective::class,
            SearchByOperatorBitwiseRightShiftDirective::class,
        ],
        self::Float     => [
            self::Number,
        ],
        self::Boolean   => [
            SearchByOperatorEqualDirective::class,
            SearchByOperatorNotEqualDirective::class,
        ],
        self::String    => [
            SearchByOperatorEqualDirective::class,
            SearchByOperatorNotEqualDirective::class,
            SearchByOperatorLikeDirective::class,
            SearchByOperatorNotLikeDirective::class,
            SearchByOperatorInDirective::class,
            SearchByOperatorNotInDirective::class,
            SearchByOperatorContainsDirective::class,
            SearchByOperatorStartsWithDirective::class,
            SearchByOperatorEndsWithDirective::class,
        ],

        // Special types
        self::Number    => [
            SearchByOperatorEqualDirective::class,
            SearchByOperatorNotEqualDirective::class,
            SearchByOperatorLessThanDirective::class,
            SearchByOperatorLessThanOrEqualDirective::class,
            SearchByOperatorGreaterThanDirective::class,
            SearchByOperatorGreaterThanOrEqualDirective::class,
            SearchByOperatorInDirective::class,
            SearchByOperatorNotInDirective::class,
            SearchByOperatorBetweenDirective::class,
            SearchByOperatorNotBetweenDirective::class,
        ],
        self::Enum      => [
            SearchByOperatorEqualDirective::class,
            SearchByOperatorNotEqualDirective::class,
            SearchByOperatorInDirective::class,
            SearchByOperatorNotInDirective::class,
        ],
        self::Null      => [
            SearchByOperatorIsNullDirective::class,
            SearchByOperatorIsNotNullDirective::class,
        ],
        self::Extra     => [
            SearchByOperatorAllOfDirective::class,
            SearchByOperatorAnyOfDirective::class,
            SearchByOperatorNotDirective::class,
        ],
        self::Condition => [
            SearchByOperatorRelationDirective::class,
            SearchByOperatorConditionDirective::class,
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
