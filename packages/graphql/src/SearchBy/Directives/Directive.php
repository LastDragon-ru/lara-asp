<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives;

use Exception;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Ast\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Builder;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Utils\ArgumentFactory;
use LastDragon_ru\LaraASP\GraphQL\Utils\Property;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Nuwave\Lighthouse\Execution\Arguments\ArgumentSet;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgBuilderDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgManipulator;
use Nuwave\Lighthouse\Support\Utils;

class Directive extends BaseDirective implements ArgManipulator, ArgBuilderDirective, Builder {
    public const Name          = 'SearchBy';
    public const ScalarID      = 'ID';
    public const ScalarInt     = 'Int';
    public const ScalarFloat   = 'Float';
    public const ScalarString  = 'String';
    public const ScalarBoolean = 'Boolean';
    public const ScalarEnum    = self::Name.'Enum';
    public const ScalarNull    = self::Name.'Null';
    public const ScalarLogic   = self::Name.'Logic';
    public const ScalarNumber  = self::Name.'Number';
    public const ArgOperators  = 'operators';

    public function __construct(
        protected Container $container,
        protected ArgumentFactory $factory,
    ) {
        // empty
    }

    public static function definition(): string {
        return /** @lang GraphQL */ <<<'GRAPHQL'
            """
            Convert Input into Search Conditions.
            """
            directive @searchBy on ARGUMENT_DEFINITION
        GRAPHQL;
    }

    public function manipulateArgDefinition(
        DocumentAST &$documentAST,
        InputValueDefinitionNode &$argDefinition,
        FieldDefinitionNode &$parentField,
        ObjectTypeDefinitionNode &$parentType,
    ): void {
        $this->container
            ->make(Manipulator::class, ['document' => $documentAST])
            ->update($this->directiveNode, $argDefinition);
    }

    /**
     * @inheritDoc
     * @return EloquentBuilder<Model>|QueryBuilder
     */
    public function handleBuilder($builder, $value): EloquentBuilder|QueryBuilder {
        $argument = $this->factory->getArgument($this->definitionNode, $value);

        if ($argument->value instanceof ArgumentSet) {
            $builder = $this->where($builder, $argument, new Property());
        } else {
            throw new Exception('fixme'); // fixme(graphql): Throw error if no definition
        }

        return $builder;
    }

    /**
     * @inheritDoc
     */
    public function where(object $builder, ArgumentSet $arguments, Property $property): object {
        // Process
        // fixme(graphql)!: only one property allowed

        foreach ($arguments->arguments as $name => $argument) {
            $property = $property->getChild($name);
            $operator = $this->getOperator($argument);
            $builder  = $operator->call($this, $builder, $property, $argument);
        }

        // Return
        return $builder;
    }

    protected function getOperator(Argument $argument): Operator {
        // Operators
        /** @var Collection<int, Operator> $operators */
        $operators = new Collection();
        $filter    = Utils::instanceofMatcher(Operator::class);

        if ($argument->value instanceof ArgumentSet) {
            foreach ($argument->value->arguments as $argument) {
                $operators = $operators->concat($argument->directives->filter($filter));
            }
        }

        if ($operators->count() > 1) {
            throw new Exception('fixme'); // fixme(graphql): Throw error if no definition
        }

        if ($operators->isEmpty()) {
            throw new Exception('fixme'); // fixme(graphql): Throw error if no definition
        }

        // Operator
        $operator = $operators->first();

        if (!($operator instanceof Operator)) {
            throw new Exception('fixme'); // fixme(graphql): Throw error if no definition
        }

        // Return
        return $operator;
    }
}
