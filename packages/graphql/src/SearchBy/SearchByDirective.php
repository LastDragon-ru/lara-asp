<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\Between;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\Equal;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\GreaterThan;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\GreaterThanOrEqual;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\In;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\LessThan;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\LessThanOrEqual;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\Like;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Complex\Relation;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical\AllOf;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical\AnyOf;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgBuilderDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgManipulator;

use function array_merge;

class SearchByDirective extends BaseDirective implements ArgManipulator, ArgBuilderDirective {
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

    protected Container $container;

    /**
     * Determines operators available for each scalar type.
     *
     * @var array<string, array<string>|string>
     */
    protected array $scalars = [
        // Standard types
        'ID'           => [
            Equal::class,
            In::class,
        ],
        'Int'          => [
            Equal::class,
            LessThan::class,
            LessThanOrEqual::class,
            GreaterThan::class,
            GreaterThanOrEqual::class,
            In::class,
            Between::class,
        ],
        'Float'        => 'Int',
        'Boolean'      => [
            Equal::class,
        ],
        'String'       => [
            Equal::class,
            Like::class,
            In::class,
        ],

        // Special types
        self::Enum     => [
            Equal::class,
            In::class,
        ],
        self::Logic    => [
            AllOf::class,
            AnyOf::class,
        ],
        self::Relation => [
            Relation::class,
            Equal::class,
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
    public function __construct(Container $container, array $scalars, array $aliases) {
        $this->container = $container;
        $this->scalars   = array_merge($this->scalars, $scalars);
        $this->aliases   = array_merge($this->aliases, $aliases);
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
            $this->container,
            $documentAST,
            self::Name,
            $this->scalars,
            $this->aliases,
        ))->getConditionsType($argDefinition);
    }

    /**
     * @inheritdoc
     */
    public function handleBuilder($builder, $value): QueryBuilder|EloquentBuilder {
        return (new SearchBuilder(
            (new Collection($this->scalars))->flatten()->unique()->all(),
        ))->build($builder, $value);
    }
}
