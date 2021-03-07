<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\Parser;
use Illuminate\Contracts\Container\Container;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Between;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Equal;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\GreaterThan;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\GreaterThanOrEqual;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\In;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\LessThan;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\LessThanOrEqual;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\NotEqual;
use Nuwave\Lighthouse\Schema\AST\ASTHelper;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgManipulator;

use function array_merge;
use function str_ends_with;

class SearchByDirective extends BaseDirective implements ArgManipulator {
    protected const NAME = 'SearchBy';

    /**
     * @var array<string, \LastDragon_ru\LaraASP\GraphQL\SearchBy\Operator>|null
     */
    protected ?array $operators = [];

    protected Container $container;

    /**
     * Determines operators available for each scalar type.
     *
     * @var array<string, array<string>|string>
     */
    protected array $scalars = [
        'ID'      => [
            Equal::class,
            NotEqual::class,
        ],
        'Int'     => [
            Equal::class,
            NotEqual::class,
            LessThan::class,
            LessThanOrEqual::class,
            GreaterThan::class,
            GreaterThanOrEqual::class,
            In::class,
            Between::class,
        ],
        'Float'   => 'Int',
        'Boolean' => [
            Equal::class,
            NotEqual::class,
        ],
        'String'  => [
            Equal::class,
            NotEqual::class,
            In::class,
        ],
    ];

    /**
     * @param array<string, array<class-string<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Operator>>> $scalars
     */
    public function __construct(Container $container, array $scalars) {
        $this->container = $container;
        $this->scalars   = array_merge($this->scalars, $scalars);
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
        $manipulator         = new Manipulator($this->container, $documentAST, self::NAME, $this->scalars);
        $argDefinition->type = $manipulator->getConditionType($argDefinition);
    }
}
