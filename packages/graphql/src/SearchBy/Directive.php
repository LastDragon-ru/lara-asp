<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use LastDragon_ru\LaraASP\GraphQL\PackageTranslator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\Between;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\Equal;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\GreaterThan;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\GreaterThanOrEqual;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\In;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\LessThan;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\LessThanOrEqual;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\Like;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\NotBetween;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\NotEqual;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\NotIn;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\NotLike;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Complex\Relation;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical\AllOf;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical\AnyOf;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical\Not;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgBuilderDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgManipulator;

use function array_merge;
use function class_exists;

class Directive extends BaseDirective implements ArgManipulator, ArgBuilderDirective {
    public const Name        = 'SearchBy';
    public const Enum        = 'Enum';
    public const Logic       = 'Logic';
    public const Relation    = 'Relation';
    public const RelationHas = 'RelationHas';
    public const TypeFlag    = 'Flag';

    /**
     * @var array<string, \LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator>|null
     */
    protected ?array $operators = [];

    /**
     * Determines operators available for each scalar type.
     *
     * @var array<string, array<string>|string>
     */
    protected array $scalars = [
        // Standard types
        'ID'           => [
            Equal::class,
            NotEqual::class,
            In::class,
            NotIn::class,
        ],
        'Int'          => [
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
        'Float'        => 'Int',
        'Boolean'      => [
            Equal::class,
        ],
        'String'       => [
            Equal::class,
            NotEqual::class,
            Like::class,
            NotLike::class,
            In::class,
            NotIn::class,
        ],

        // Special types
        self::Enum     => [
            Equal::class,
            NotEqual::class,
            In::class,
            NotIn::class,
        ],
        self::Logic    => [
            AllOf::class,
            AnyOf::class,
            Not::class,
        ],
        self::Relation => [
            Relation::class,
            Equal::class,
            NotEqual::class,
            LessThan::class,
            LessThanOrEqual::class,
            GreaterThan::class,
            GreaterThanOrEqual::class,
        ],
    ];

    /**
     * Allow redefine scalar type in conditions.
     *
     * @var array<string,string>
     */
    protected array $aliases = [
        self::RelationHas => 'Int',
    ];

    /**
     * @param array<string, array<class-string<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator>>> $scalars
     * @param array<string,string>                                                                           $aliases
     */
    public function __construct(
        protected Container $container,
        protected PackageTranslator $translator,
        array $scalars,
        array $aliases,
    ) {
        $this->scalars = array_merge($this->scalars, $scalars);
        $this->aliases = array_merge($this->aliases, $aliases);
    }

    public static function definition(): string {
        return /** @lang GraphQL */ <<<'GRAPHQL'
            """
            Convert Input into Search Conditions.
            """
            directive @searchBy on INPUT_FIELD_DEFINITION
        GRAPHQL;
    }

    public function manipulateArgDefinition(
        DocumentAST &$documentAST,
        InputValueDefinitionNode &$argDefinition,
        FieldDefinitionNode &$parentField,
        ObjectTypeDefinitionNode &$parentType,
    ): void {
        $argDefinition->type = (new AstManipulator(
            $documentAST,
            $this->container,
            self::Name,
            $this->scalars,
            $this->aliases,
        ))->getType($argDefinition);
    }

    /**
     * @inheritdoc
     */
    public function handleBuilder($builder, $value): EloquentBuilder|QueryBuilder {
        return (new SearchBuilder(
            $this->translator,
            (new Collection($this->scalars))
                ->flatten()
                ->unique()
                ->filter(static function (string $operator): bool {
                    return class_exists($operator);
                })->map(function (string $operator): object {
                    return $this->container->make($operator);
                })
                ->all(),
        ))->build($builder, $value);
    }
}
