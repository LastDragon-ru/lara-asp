<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Directives;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Clause;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Eloquent\Builder as SortByEloquentBuilder;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Query\Builder as SortByQueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Scout\Builder as SortByScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions\Client\SortClauseEmpty;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions\Client\SortClauseTooManyProperties;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Manipulator;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Scout\ScoutBuilderDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgBuilderDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgManipulator;

use function array_shift;
use function count;
use function is_array;
use function key;

class Directive extends BaseDirective implements ArgManipulator, ArgBuilderDirective, ScoutBuilderDirective {
    public const Name          = 'SortBy';
    public const TypeDirection = 'SortByDirection';

    public function __construct(
        protected Container $container,
    ) {
        // empty
    }

    public static function definition(): string {
        return /** @lang GraphQL */ <<<'GRAPHQL'
            """
            Convert Input into Sort Clause.
            """
            directive @sortBy on ARGUMENT_DEFINITION
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
            ->update($argDefinition, $parentField);
    }

    /**
     * @inheritDoc
     * @return EloquentBuilder<Model>|QueryBuilder
     */
    public function handleBuilder($builder, mixed $value): EloquentBuilder|QueryBuilder {
        return $builder instanceof EloquentBuilder
            ? $this->container->make(SortByEloquentBuilder::class)->handle($builder, $this->getClauses($value))
            : $this->container->make(SortByQueryBuilder::class)->handle($builder, $this->getClauses($value));
    }

    public function handleScoutBuilder(ScoutBuilder $builder, mixed $value): ScoutBuilder {
        return $this->container->make(SortByScoutBuilder::class)->handle($builder, $this->getClauses($value));
    }

    /**
     * @param array<array<string,string|array<string,string|array<string,string|null>>>> $clauses
     *
     * @return array<Clause>
     */
    protected function getClauses(array $clauses): array {
        // $value = [
        //      ['a' => 'desc']
        //      ['a' => ['b' => 'ask']]
        // ]
        $parsed = [];

        foreach ($clauses as $index => $clause) {
            // Parse
            $path      = [];
            $direction = null;

            do {
                // Empty?
                if (!$clause) {
                    throw new SortClauseEmpty($index, $clauses[$index]);
                }

                // More than one property?
                if (count($clause) > 1) {
                    throw new SortClauseTooManyProperties($index, $clauses[$index]);
                }

                // Process
                $path[] = key($clause);
                $clause = array_shift($clause);

                if (!is_array($clause)) {
                    $direction = $clause;
                    $clause    = null;
                } elseif (!$clause) {
                    throw new SortClauseEmpty($index, $clauses[$index]);
                } else {
                    // empty
                }
            } while ($clause);

            // Save
            $parsed[] = new Clause($path, $direction);
        }

        return $parsed;
    }
}
