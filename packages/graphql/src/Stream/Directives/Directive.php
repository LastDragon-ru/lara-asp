<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Directives;

use Exception;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use Illuminate\Database\Eloquent\Builder;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\BuilderInfoProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InterfaceFieldSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectFieldSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\WithManipulator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\WithSource;
use LastDragon_ru\LaraASP\GraphQL\Package;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByDirective;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Definitions\SortByDirective;
use LastDragon_ru\LaraASP\GraphQL\Stream\Definitions\StreamChunkDirective;
use LastDragon_ru\LaraASP\GraphQL\Stream\Definitions\StreamCursorDirective;
use LastDragon_ru\LaraASP\GraphQL\Stream\Exceptions\FailedToCreateStreamFieldIsNotList;
use LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\Directive as DirectiveContract;
use Nuwave\Lighthouse\Support\Contracts\FieldManipulator;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;

use function config;

class Directive extends BaseDirective implements FieldResolver, FieldManipulator, BuilderInfoProvider {
    use WithManipulator;
    use WithSource;

    public const    Name          = 'Stream';
    protected const ArgSearchable = 'searchable';
    protected const ArgSortable   = 'sortable';

    public static function definition(): string {
        $name       = DirectiveLocator::directiveName(static::class);
        $sortable   = static::ArgSortable;
        $searchable = static::ArgSearchable;

        return <<<GraphQL
            """
            Splits list of items into the chunks and return one chunk specified by page number or cursor.
            """
            directive @{$name}(
                {$searchable}: Boolean
                {$sortable}: Boolean
            ) on FIELD_DEFINITION
        GraphQL;
    }

    // <editor-fold desc="FieldManipulator">
    // =========================================================================
    public function manipulateFieldDefinition(
        DocumentAST &$documentAST,
        FieldDefinitionNode &$fieldDefinition,
        ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode &$parentType,
    ): void {
        // Prepare
        $manipulator = $this->getAstManipulator($documentAST);
        $source      = $this->getFieldSource($manipulator, $parentType, $fieldDefinition);
        $prefix      = Package::Name.'.stream';

        // Field is a list?
        if (!$source->isList()) {
            throw new FailedToCreateStreamFieldIsNotList($source);
        }

        // Searchable?
        $searchable = $this->directiveArgValue(static::ArgSearchable)
            ?? (bool) config("{$prefix}.search.enabled");

        if ($searchable === null || $searchable === true) {
            $this->addArgument(
                $manipulator,
                $source,
                SearchByDirective::class,
                Cast::toString(config("{$prefix}.search.name") ?: 'where'),
                $manipulator::Placeholder,
            );
        }

        // Sortable?
        $sortable = $this->directiveArgValue(static::ArgSortable)
            ?? (bool) config("{$prefix}.sort.enabled");

        if ($sortable === null || $sortable === true) {
            $this->addArgument(
                $manipulator,
                $source,
                SortByDirective::class,
                Cast::toString(config("{$prefix}.sort.name") ?: 'order'),
                $manipulator::Placeholder,
            );
        }

        // Chunk
        $this->addArgument(
            $manipulator,
            $source,
            StreamChunkDirective::class,
            Cast::toString(config("{$prefix}.chunk.name") ?: 'chunk'),
            $manipulator::Placeholder,
            null,
            null,
            [
                StreamChunkDirective::ArgMax     => config("{$prefix}.chunk.max") ?: 100,
                StreamChunkDirective::ArgDefault => config("{$prefix}.chunk.default") ?: 25,
            ],
        );

        // Cursor
        $this->addArgument(
            $manipulator,
            $source,
            StreamCursorDirective::class,
            Cast::toString(config("{$prefix}.cursor.name") ?: 'cursor'),
            $manipulator::Placeholder,
        );

        // Update type
        // fixme(graphql/@stream)!: Update type: not implemented

        // Interfaces (same as @searchBy/@sortBy)
        // fixme(graphql/@stream)!: Interfaces: not implemented
    }

    /**
     * @param class-string<DirectiveContract> $directive
     * @param array<string, mixed>            $arguments
     */
    protected function addArgument(
        AstManipulator $manipulator,
        ObjectFieldSource|InterfaceFieldSource $field,
        string $directive,
        string $name,
        string $type,
        string $value = null,
        string $description = null,
        array $arguments = [],
    ): void {
        // Arguments with directive already exists?
        $argument = $manipulator->findArgument(
            $field->getField(),
            static function (mixed $argument) use ($manipulator, $directive): bool {
                return $manipulator->getDirective($argument, $directive) !== null;
            },
        );

        if ($argument && !$manipulator->isDeprecated($argument)) {
            return;
        }

        // Nope
        $manipulator->addDirective(
            $manipulator->addArgument($field->getObject(), $field->getField(), $name, $type, $value, $description),
            $directive,
            $arguments,
        );
    }
    // </editor-fold>

    // <editor-fold desc="BuilderInfoProvider">
    // =========================================================================
    public function getBuilderInfo(TypeSource $source): ?BuilderInfo {
        // fixme(graphql)!: Not implemented.

        return BuilderInfo::create(Builder::class);
    }
    // </editor-fold>

    // <editor-fold desc="FieldResolver">
    // =========================================================================
    public function resolveField(FieldValue $fieldValue): callable {
        // fixme(graphql)!: Not implemented.

        return static fn () => throw new Exception('Not implemented.');
    }
    // </editor-fold>
}
