<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\PackageTranslator;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgBuilderDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgManipulator;

class Directive extends BaseDirective implements ArgManipulator, ArgBuilderDirective {
    public const Name          = 'SortBy';
    public const TypeDirection = 'Direction';

    public function __construct(
        protected Container $container,
        protected PackageTranslator $translator,
        protected DirectiveLocator $directives,
    ) {
        // empty
    }

    public static function definition(): string {
        return /** @lang GraphQL */ <<<'GRAPHQL'
            """
            Convert Input into Sort Clause.
            """
            directive @sortBy on INPUT_FIELD_DEFINITION
        GRAPHQL;
    }

    public function manipulateArgDefinition(
        DocumentAST &$documentAST,
        InputValueDefinitionNode &$argDefinition,
        FieldDefinitionNode &$parentField,
        ObjectTypeDefinitionNode &$parentType,
    ): void {
        $this->container
            ->make(AstManipulator::class, ['document' => $documentAST])
            ->update($argDefinition);
    }

    /**
     * @inheritdoc
     */
    public function handleBuilder($builder, $value): EloquentBuilder|QueryBuilder {
        return (new SortBuilder($this->translator))->build($builder, $value);
    }
}
