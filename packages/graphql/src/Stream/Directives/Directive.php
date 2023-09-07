<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Directives;

use Exception;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\Parser;
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
use LastDragon_ru\LaraASP\GraphQL\Stream\Types\Stream;
use LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\Directive as DirectiveContract;
use Nuwave\Lighthouse\Support\Contracts\FieldManipulator;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;
use stdClass;

use function config;
use function json_encode;
use function str_starts_with;

use const JSON_THROW_ON_ERROR;

class Directive extends BaseDirective implements FieldResolver, FieldManipulator, BuilderInfoProvider {
    use WithManipulator;
    use WithSource;

    final public const Name          = 'Stream';
    final public const Settings      = Package::Name.'.stream';
    final public const ArgSearchable = 'searchable';
    final public const ArgSortable   = 'sortable';
    final public const ArgChunk      = 'chunk';

    public static function definition(): string {
        $name          = DirectiveLocator::directiveName(static::class);
        $argSearchable = self::ArgSearchable;
        $argSortable   = self::ArgSortable;
        $argChunk      = self::ArgChunk;

        return <<<GRAPHQL
            """
            Splits list of items into the chunks and return one chunk specified by page number or cursor.
            """
            directive @{$name}(
                {$argSearchable}: Boolean
                {$argSortable}: Boolean
                {$argChunk}: Int
            ) on FIELD_DEFINITION
        GRAPHQL;
    }

    // <editor-fold desc="FieldManipulator">
    // =========================================================================
    public function manipulateFieldDefinition(
        DocumentAST &$documentAST,
        FieldDefinitionNode &$fieldDefinition,
        ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode &$parentType,
    ): void {
        // Prepare
        $builder     = new BuilderInfo('fixme', stdClass::class); // fixme(graphql/@stream)!: BuilderInfo
        $manipulator = $this->getManipulator($documentAST, $builder);
        $source      = $this->getFieldSource($manipulator, $parentType, $fieldDefinition);
        $prefix      = self::Settings;

        // Updated?
        if (str_starts_with($manipulator->getTypeName($fieldDefinition), self::Name)) {
            return;
        }

        // Field is a list?
        if (!$source->isList()) {
            throw new FailedToCreateStreamFieldIsNotList($source);
        }

        // Searchable?
        $searchable = Cast::toBool(
            $this->directiveArgValue(self::ArgSearchable)
            ?? config("{$prefix}.search.enabled")
            ?? false,
        );

        if ($searchable) {
            $this->addArgument(
                $manipulator,
                $source,
                SearchByDirective::class,
                Cast::toString(config("{$prefix}.search.name") ?: 'where'),
                $manipulator::Placeholder,
            );
        }

        // Sortable?
        $sortable = Cast::toBool(
            $this->directiveArgValue(self::ArgSortable)
            ?? config("{$prefix}.sort.enabled")
            ?? false,
        );

        if ($sortable) {
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
            StreamChunkDirective::settings()['name'],
            $manipulator::Placeholder,
            null,
            null,
            $this->directiveArgValue(self::ArgChunk) !== null
                ? [Chunk::ArgSize => $this->directiveArgValue(self::ArgChunk)]
                : [],
        );

        // Cursor
        $this->addArgument(
            $manipulator,
            $source,
            StreamCursorDirective::class,
            StreamCursorDirective::settings()['name'],
            $manipulator::Placeholder,
        );

        // Update type
        $type = $manipulator->getType(Stream::class, $source);
        $type = Parser::typeReference("{$type}!");

        $manipulator->setFieldType(
            $parentType,
            $fieldDefinition,
            $type,
        );
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

        if ($argument && $arguments) {
            // todo(graphql/@stream): Move to AstManipulator + check definition.
            $directiveNode = $manipulator->getDirective($argument, $directive);
            $directiveNode = $directiveNode
                ? $manipulator->getDirectiveNode($directiveNode)
                : null;

            if ($directiveNode) {
                foreach ($arguments as $argName => $argValue) {
                    $argNode  = $manipulator->getArgument($directiveNode, $argName);
                    $argValue = json_encode($argValue, JSON_THROW_ON_ERROR);

                    if ($argNode) {
                        $argNode->value = Parser::valueLiteral($argValue);
                    } else {
                        $directiveNode->arguments[] = Parser::argument("{$argName}: {$argValue}");
                    }
                }
            }
        }

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
