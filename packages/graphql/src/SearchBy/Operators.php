<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy;

use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator as BuilderOperator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Operators as BuilderOperators;
use LastDragon_ru\LaraASP\GraphQL\Package;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Scope;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorAllOfDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorAnyOfDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorBetweenDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorBitwiseAndDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorBitwiseLeftShiftDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorBitwiseOrDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorBitwiseRightShiftDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorBitwiseXorDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorChildDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorContainsDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorEndsWithDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorEqualDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorFieldDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorGreaterThanDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorGreaterThanOrEqualDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorInDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorIsNotNullDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorIsNullDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorLessThanDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorLessThanOrEqualDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorLikeDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorNotBetweenDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorNotContainsDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorNotDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorNotEndsWithDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorNotEqualDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorNotInDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorNotLikeDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorNotStartsWithDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorRelationshipDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorStartsWithDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator;
use Override;

use function array_merge;
use function config;

class Operators extends BuilderOperators {
    private const Prefix   = Directive::Name.'Operators';
    public const  ID       = self::Prefix.Type::ID;
    public const  Int      = self::Prefix.Type::INT;
    public const  Float    = self::Prefix.Type::FLOAT;
    public const  String   = self::Prefix.Type::STRING;
    public const  Boolean  = self::Prefix.Type::BOOLEAN;
    public const  Null     = self::Prefix.'Null';
    public const  Extra    = self::Prefix.'Extra';
    public const  Number   = self::Prefix.'Number';
    public const  Enum     = self::Prefix.'Enum';
    public const  Object   = self::Prefix.'Object';
    public const  Disabled = self::Prefix.'Disabled';

    /**
     * @inheritDoc
     */
    protected array $default = [
        // Built-in
        Type::ID      => [
            self::ID,
        ],
        Type::INT     => [
            self::Int,
        ],
        Type::FLOAT   => [
            self::Float,
        ],
        Type::STRING  => [
            self::String,
        ],
        Type::BOOLEAN => [
            self::Boolean,
        ],

        // Scalars
        self::ID      => [
            SearchByOperatorEqualDirective::class,
            SearchByOperatorNotEqualDirective::class,
            SearchByOperatorInDirective::class,
            SearchByOperatorNotInDirective::class,
        ],
        self::Int     => [
            self::Number,
            SearchByOperatorBitwiseOrDirective::class,
            SearchByOperatorBitwiseXorDirective::class,
            SearchByOperatorBitwiseAndDirective::class,
            SearchByOperatorBitwiseLeftShiftDirective::class,
            SearchByOperatorBitwiseRightShiftDirective::class,
        ],
        self::Float   => [
            self::Number,
        ],
        self::Boolean => [
            SearchByOperatorEqualDirective::class,
            SearchByOperatorNotEqualDirective::class,
        ],
        self::String  => [
            SearchByOperatorEqualDirective::class,
            SearchByOperatorNotEqualDirective::class,
            SearchByOperatorLikeDirective::class,
            SearchByOperatorNotLikeDirective::class,
            SearchByOperatorInDirective::class,
            SearchByOperatorNotInDirective::class,
            SearchByOperatorContainsDirective::class,
            SearchByOperatorNotContainsDirective::class,
            SearchByOperatorStartsWithDirective::class,
            SearchByOperatorNotStartsWithDirective::class,
            SearchByOperatorEndsWithDirective::class,
            SearchByOperatorNotEndsWithDirective::class,
        ],

        // Special
        self::Number  => [
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
        self::Enum    => [
            SearchByOperatorEqualDirective::class,
            SearchByOperatorNotEqualDirective::class,
            SearchByOperatorInDirective::class,
            SearchByOperatorNotInDirective::class,
        ],
        self::Null    => [
            SearchByOperatorIsNullDirective::class,
            SearchByOperatorIsNotNullDirective::class,
        ],
        self::Extra   => [
            SearchByOperatorFieldDirective::class,
            SearchByOperatorAllOfDirective::class,
            SearchByOperatorAnyOfDirective::class,
            SearchByOperatorNotDirective::class,
        ],
        self::Object  => [
            SearchByOperatorRelationshipDirective::class,
            SearchByOperatorChildDirective::class,
        ],

        // Lighthouse
        'Date'        => [
            self::Number,
        ],
        'DateTime'    => [
            'Date',
        ],
        'DateTimeTz'  => [
            'Date',
        ],
        'DateTimeUtc' => [
            'Date',
        ],
    ];

    public function __construct() {
        /** @var array<string, list<class-string<BuilderOperator>|string>> $operators */
        $operators = (array) config(Package::Name.'.search_by.operators');

        parent::__construct($operators);
    }

    #[Override]
    public function getScope(): string {
        return Scope::class;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    protected function getDisabledOperators(AstManipulator $manipulator): array {
        return array_merge(
            parent::getDisabledOperators($manipulator),
            $this->getTypeOperators($manipulator, self::Disabled),
        );
    }
}
