<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use LastDragon_ru\LaraASP\GraphQL\PackageTranslator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\AstManipulator;
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
use LastDragon_ru\LaraASP\GraphQL\SearchBy\SearchBuilder;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgBuilderDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgManipulator;

use function array_merge;

class Directive extends BaseDirective implements ArgManipulator, ArgBuilderDirective {
    public const Name          = 'SearchBy';
    public const ScalarID      = 'ID';
    public const ScalarInt     = 'Int';
    public const ScalarFloat   = 'Float';
    public const ScalarString  = 'String';
    public const ScalarBoolean = 'Boolean';
    public const ScalarEnum    = self::Name.'Enum';
    public const ScalarNull    = self::Name.'Null';
    public const ScalarLogic   = self::Name.'Logic';
    public const ArgOperators  = 'operators';
    public const TypeFlag      = 'Flag';

    /**
     * Determines operators available for each scalar type.
     *
     * @var array<string, array<string>|string>
     */
    protected array $scalars = [
        // Standard types
        self::ScalarID      => [
            Equal::class,
            NotEqual::class,
            In::class,
            NotIn::class,
        ],
        self::ScalarInt     => [
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
        self::ScalarFloat   => self::ScalarInt,
        self::ScalarBoolean => [
            Equal::class,
        ],
        self::ScalarString  => [
            Equal::class,
            NotEqual::class,
            Like::class,
            NotLike::class,
            In::class,
            NotIn::class,
        ],

        // Special types
        self::ScalarEnum    => [
            Equal::class,
            NotEqual::class,
            In::class,
            NotIn::class,
        ],
        self::ScalarNull    => [
            IsNull::class,
            IsNotNull::class,
        ],
        self::ScalarLogic   => [
            AllOf::class,
            AnyOf::class,
            Not::class,
        ],
    ];

    /**
     * @param array<string, array<class-string<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator>>> $scalars
     */
    public function __construct(
        protected Container $container,
        protected PackageTranslator $translator,
        protected DirectiveLocator $directives,
        array $scalars,
    ) {
        $this->scalars = array_merge($this->scalars, $scalars);
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
        (new AstManipulator(
            $this->directives,
            $documentAST,
            $this->container,
            $this->scalars,
        ))->update($this->directiveNode, $argDefinition);
    }

    /**
     * @inheritdoc
     */
    public function handleBuilder($builder, $value): EloquentBuilder|QueryBuilder {
        $operators = $this->directiveArgValue(self::ArgOperators);
        $operators = (new Collection($operators))
            ->map(function (string $operator): object {
                return $this->container->make($operator);
            })
            ->all();

        return (new SearchBuilder(
            $this->translator,
            $operators,
        ))->build($builder, $value);
    }
}
