<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Directives;

use Closure;
use Exception;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\HasFieldsType;
use GraphQL\Type\Definition\Type;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Eloquent\ModelHelper;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfoDetector;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\BuilderInfoProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InterfaceFieldArgumentSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InterfaceFieldSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectFieldArgumentSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectFieldSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\WithManipulator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\WithSource;
use LastDragon_ru\LaraASP\GraphQL\Package;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByDirective;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Definitions\SortByDirective;
use LastDragon_ru\LaraASP\GraphQL\Stream\Definitions\StreamChunkDirective;
use LastDragon_ru\LaraASP\GraphQL\Stream\Definitions\StreamCursorDirective;
use LastDragon_ru\LaraASP\GraphQL\Stream\Exceptions\FailedToCreateStreamFieldIsNotList;
use LastDragon_ru\LaraASP\GraphQL\Stream\Exceptions\FailedToCreateStreamFieldIsSubscription;
use LastDragon_ru\LaraASP\GraphQL\Stream\Exceptions\FailedToCreateStreamFieldIsUnion;
use LastDragon_ru\LaraASP\GraphQL\Stream\Exceptions\FailedToCreateStreamKeyUnknown;
use LastDragon_ru\LaraASP\GraphQL\Stream\Types\Stream;
use LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Directives\RenameDirective;
use Nuwave\Lighthouse\Schema\ResolverProvider;
use Nuwave\Lighthouse\Schema\RootType;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\Directive as DirectiveContract;
use Nuwave\Lighthouse\Support\Contracts\FieldManipulator;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Nuwave\Lighthouse\Support\Contracts\ProvidesResolver;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionNamedType;

use function class_exists;
use function config;
use function explode;
use function is_a;
use function is_array;
use function is_string;
use function json_encode;

use const JSON_THROW_ON_ERROR;

class Directive extends BaseDirective implements FieldResolver, FieldManipulator, BuilderInfoProvider {
    use WithManipulator;
    use WithSource;

    final public const Name          = 'Stream';
    final public const Settings      = Package::Name.'.stream';
    final public const ArgSearchable = 'searchable';
    final public const ArgSortable   = 'sortable';
    final public const ArgBuilder    = 'builder';
    final public const ArgChunk      = 'chunk';
    final public const ArgKey        = 'key';

    public static function definition(): string {
        $name          = DirectiveLocator::directiveName(static::class);
        $builder       = self::Name.'Builder';
        $argSearchable = self::ArgSearchable;
        $argSortable   = self::ArgSortable;
        $argBuilder    = self::ArgBuilder;
        $argChunk      = self::ArgChunk;
        $argKey        = self::ArgKey;

        return <<<GRAPHQL
            """
            Splits list of items into the chunks and return one chunk specified
            by a page number or a cursor.
            """
            directive @{$name}(
                """
                Overrides default searchable status.
                """
                {$argSearchable}: Boolean

                """
                Overrides default sortable status.
                """
                {$argSortable}: Boolean

                """
                Overrides default builder. Useful if the standard detection
                algorithm doesn't fit/work. By default, the directive will use
                the field and its type to determine the Builder to query.
                """
                {$argBuilder}: {$builder}

                """
                Overrides default chunk size.
                """
                {$argChunk}: Int

                """
                Overrides default unique key. Useful if the standard detection
                algorithm doesn't fit/work. By default, the directive will use
                the name of field with `ID!` type.
                """
                {$argKey}: String
            ) on FIELD_DEFINITION

            """
            Explicit builder. Only one of fields allowed.
            """
            input {$builder} {
                """
                The class name of the model to query.
                """
                model: String

                """
                The reference to a function that provides a Builder instance.
                """
                builder: String

                """
                The relation name to query.
                """
                relation: String
            }
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
        $manipulator = $this->getAstManipulator($documentAST);
        $source      = $this->getFieldSource($manipulator, $parentType, $fieldDefinition);
        $prefix      = self::Settings;

        // Updated?
        if (Stream::is($source->getTypeName())) {
            return;
        }

        // Subscription?
        if ($source->getParent()->getTypeName() === RootType::SUBSCRIPTION) {
            throw new FailedToCreateStreamFieldIsSubscription($source);
        }

        // Field is a list?
        if (!$source->isList()) {
            throw new FailedToCreateStreamFieldIsNotList($source);
        }

        // Union?
        if ($source->isUnion()) {
            throw new FailedToCreateStreamFieldIsUnion($source);
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
        $detector = Container::getInstance()->make(BuilderInfoDetector::class);
        $builder  = $detector->getFieldBuilderInfo($documentAST, $parentType, $fieldDefinition);
        $type     = $this->getManipulator($documentAST, $builder)->getType(Stream::class, $source);
        $type     = Parser::typeReference("{$type}!");

        $manipulator->setFieldType(
            $parentType,
            $fieldDefinition,
            $type,
        );

        // Key? (required)
        $this->getArgKey($manipulator, $source);
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
        // Resolver?
        $resolver = null;

        if ($source instanceof ObjectFieldArgumentSource || $source instanceof InterfaceFieldArgumentSource) {
            $resolver = $this->getResolver($source->getParent());
        } elseif ($source instanceof ObjectFieldSource || $source instanceof InterfaceFieldSource) {
            $resolver = $this->getResolver($source);
        } else {
            // empty
        }

        if ($resolver === null) {
            return null;
        }

        // Type
        $type = null;

        try {
            $type = is_array($resolver)
                ? (new ReflectionClass($resolver[0]))->getMethod($resolver[1])->getReturnType()
                : (new ReflectionFunction($resolver))->getReturnType();
            $type = $type instanceof ReflectionNamedType
                ? $type->getName()
                : null;
            $type = $type && class_exists($type) && $this->isBuilderSupported($type)
                ? $type
                : null;
        } catch (ReflectionException) {
            // empty
        }

        if ($type !== null) {
            $type = BuilderInfo::create($type);
        }

        return $type;
    }

    /**
     * @phpstan-assert-if-true class-string<EloquentBuilder<Model>|QueryBuilder|ScoutBuilder> $builder
     *
     * @param class-string $builder
     */
    protected function isBuilderSupported(string $builder): bool {
        return is_a($builder, EloquentBuilder::class, true)
            || is_a($builder, QueryBuilder::class, true)
            || is_a($builder, ScoutBuilder::class, true);
    }
    // </editor-fold>

    // <editor-fold desc="FieldResolver">
    // =========================================================================
    public function resolveField(FieldValue $fieldValue): callable {
        // fixme(graphql)!: Not implemented.

        return static fn () => throw new Exception('Not implemented.');
    }

    /**
     * @return Closure(mixed, array<string, mixed>, GraphQLContext, ResolveInfo):mixed|array{class-string, string}|null
     */
    protected function getResolver(ObjectFieldSource|InterfaceFieldSource $source): Closure|array|null {
        $resolver = null;
        $builder  = (array) $this->directiveArgValue(self::ArgBuilder);

        if ($builder) {
            if (isset($builder['builder'])) {
                $resolver = is_string($builder['builder'])
                    ? $this->getResolverClass($builder['builder'])
                    : null;
            } elseif (isset($builder['model'])) {
                $resolver = is_string($builder['model'])
                    ? $this->getResolverModel($builder['model'])
                    : null;
            } elseif (isset($builder['relation'])) {
                $resolver = is_string($builder['relation'])
                    ? $this->getResolverRelation($source->getParent()->getTypeName(), $builder['relation'])
                    : null;
            } else {
                // empty
            }
        } else {
            $parent   = $source->getParent()->getTypeName();
            $resolver = $this->getResolverQuery($parent, $source->getName()) ?? (
                RootType::isRootType($parent)
                    ? $this->getResolverModel(Stream::getOriginalTypeName($source->getTypeName()))
                    : $this->getResolverRelation($parent, $source->getName())
            );
        }

        return $resolver;
    }

    /**
     * @return Closure(mixed, array<string, mixed>, GraphQLContext, ResolveInfo): EloquentBuilder<Model>|null
     */
    protected function getResolverRelation(string $model, string $relation): ?Closure {
        $class    = $this->namespaceModelClass($model);
        $resolver = null;

        if ((new ModelHelper($class))->isRelation($relation)) {
            $resolver = static function (mixed $root) use ($class, $relation): EloquentBuilder {
                // In runtime, we cannot guarantee that the `$root` is the
                // expected model. So we are checking it explicitly and return
                // the empty Builder if the model is wrong.
                return $root instanceof $class
                    ? (new ModelHelper($root))->getRelation($relation)->getQuery()
                    : (new ModelHelper($class))->getRelation($relation)->getQuery()->whereRaw('0 = 1');
            };
        }

        return $resolver;
    }

    /**
     * @return array{class-string, string}|null
     */
    protected function getResolverQuery(string $type, string $field): ?array {
        // We are mimicking to default Lighthouse resolver resolution, thus
        // custom implementations may not work.
        $provider = Container::getInstance()->get(ProvidesResolver::class);

        if (!($provider instanceof ResolverProvider)) {
            return null;
        }

        // Determine class
        $method   = '__invoke';
        $value    = new class($type, $field) extends FieldValue {
            /**
             * @noinspection             PhpMissingParentConstructorInspection
             * @phpstan-ignore-next-line no need to call parent `__construct`
             */
            public function __construct(
                private readonly string $typeName,
                private readonly string $fieldName,
            ) {
                // no need to call parent
            }

            public function getParentName(): string {
                return $this->typeName;
            }

            public function getFieldName(): string {
                return $this->fieldName;
            }
        };
        $helper   = new class() extends ResolverProvider {
            /**
             * @return class-string|null
             */
            public function getResolverClass(ResolverProvider $provider, FieldValue $value, string $method): ?string {
                return $provider->findResolverClass($value, $method);
            }
        };
        $class    = $helper->getResolverClass($provider, $value, $method);
        $resolver = $class ? [$class, $method] : null;

        return $resolver;
    }

    /**
     * @return Closure(mixed, array<string, mixed>, GraphQLContext, ResolveInfo): EloquentBuilder<Model>
     */
    protected function getResolverModel(string $model): Closure {
        $class    = $this->namespaceModelClass($model);
        $resolver = static function () use ($class): EloquentBuilder {
            return $class::query();
        };

        return $resolver;
    }

    /**
     * @return array{class-string, string}
     */
    protected function getResolverClass(string $class): array {
        [$class, $method] = explode('@', $class, 2) + [null, null];
        $class            = $this->namespaceClassName($class ?? '');
        $resolver         = [$class, $method ?? '__invoke'];

        return $resolver;
    }
    // </editor-fold>

    // <editor-fold desc="Arguments">
    // =========================================================================
    protected function getArgKey(
        AstManipulator $manipulator,
        ObjectFieldSource|InterfaceFieldSource $source,
    ): string {
        // Explicit?
        $key = $this->directiveArgValue(self::ArgKey);

        if ($key !== null) {
            if (!is_string($key) || $key === '') {
                throw new FailedToCreateStreamKeyUnknown($source);
            }

            return $key;
        }

        // Search for field with `ID!` type
        $type  = Stream::getOriginalTypeName($source->getTypeName());
        $type  = $manipulator->getTypeDefinition($type);
        $field = null;

        if (
            $type instanceof HasFieldsType
            || $type instanceof InterfaceTypeDefinitionNode
            || $type instanceof ObjectTypeDefinitionNode
        ) {
            $field = $manipulator->findField($type, static function (mixed $field) use ($manipulator): bool {
                return !$manipulator->isList($field)
                    && !$manipulator->isNullable($field)
                    && $manipulator->getTypeName($field) === Type::ID;
            });
        }

        // Key
        if ($field) {
            $rename = $manipulator->getDirective($field, RenameDirective::class);
            $key    = $rename
                ? $rename->attributeArgValue()
                : $manipulator->getName($field);
        }

        // Found?
        if (!$key) {
            throw new FailedToCreateStreamKeyUnknown($source);
        }

        // Return
        return $key;
    }
    //</editor-fold>
}
