<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy;

use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\GraphQL\Builder\Operators as BuilderOperators;
use LastDragon_ru\LaraASP\GraphQL\PackageConfig;
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

class Operators extends BuilderOperators {
    private const string Prefix   = Directive::Name.'Operators';
    public const  string ID       = self::Prefix.Type::ID;
    public const  string Int      = self::Prefix.Type::INT;
    public const  string Float    = self::Prefix.Type::FLOAT;
    public const  string String   = self::Prefix.Type::STRING;
    public const  string Boolean  = self::Prefix.Type::BOOLEAN;
    public const  string Null     = self::Prefix.'Null';
    public const  string Extra    = self::Prefix.'Extra';
    public const  string Number   = self::Prefix.'Number';
    public const  string Enum     = self::Prefix.'Enum';
    public const  string Scalar   = self::Prefix.'Scalar';
    public const  string Object   = self::Prefix.'Object';
    public const  string Disabled = self::Prefix.'Disabled';

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
        self::Scalar  => [
            SearchByOperatorEqualDirective::class,
            SearchByOperatorNotEqualDirective::class,
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

    public function __construct(
        ContainerResolver $container,
        protected readonly PackageConfig $config,
    ) {
        parent::__construct($container, $this->config->getInstance()->searchBy->operators);
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
