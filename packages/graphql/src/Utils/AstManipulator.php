<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Utils;

use Closure;
use GraphQL\Language\AST\ArgumentNode;
use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\EnumTypeDefinitionNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\ListTypeNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\NameNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NonNullTypeNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\ScalarTypeDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Language\AST\UnionTypeDefinitionNode;
use GraphQL\Language\BlockString;
use GraphQL\Language\Parser;
use GraphQL\Language\Printer;
use GraphQL\Type\Definition\Argument;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\EnumValueDefinition;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\HasFieldsType;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\NullableType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnionType;
use GraphQL\Type\Definition\WrappingType;
use GraphQL\Utils\AST;
use Illuminate\Support\Str;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\GraphQL\Directives\Definitions\TypeDirective;
use LastDragon_ru\LaraASP\GraphQL\Exceptions\ArgumentAlreadyDefined;
use LastDragon_ru\LaraASP\GraphQL\Exceptions\NotImplemented;
use LastDragon_ru\LaraASP\GraphQL\Exceptions\TypeDefinitionAlreadyDefined;
use LastDragon_ru\LaraASP\GraphQL\Exceptions\TypeDefinitionUnknown;
use LastDragon_ru\LaraASP\GraphQL\Exceptions\TypeUnexpected;
use LastDragon_ru\LaraASP\GraphQL\Stream\Directives\Directive as StreamDirective;
use Nuwave\Lighthouse\Pagination\PaginateDirective;
use Nuwave\Lighthouse\Schema\AST\ASTHelper;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Directives\DeprecatedDirective;
use Nuwave\Lighthouse\Schema\Directives\RelationDirective;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Nuwave\Lighthouse\Support\Contracts\Directive;

use function array_merge;
use function assert;
use function in_array;
use function is_string;
use function json_encode;
use function mb_strlen;
use function mb_substr;
use function sprintf;
use function trim;

use const JSON_THROW_ON_ERROR;

class AstManipulator {
    public const string Placeholder = '_';

    public function __construct(
        private DirectiveLocator $directiveLocator,
        private DocumentAST $document,
        private TypeRegistry $types,
    ) {
        // empty
    }

    // <editor-fold desc="Getters & Setters">
    // =========================================================================
    protected function getDirectiveLocator(): DirectiveLocator {
        return $this->directiveLocator;
    }

    public function getDocument(): DocumentAST {
        return $this->document;
    }

    protected function getTypes(): TypeRegistry {
        return $this->types;
    }
    // </editor-fold>

    // <editor-fold desc="AST Helpers">
    // =========================================================================
    public function isStandard(
        (TypeDefinitionNode&Node)|(TypeNode&Node)|FieldDefinitionNode|InputValueDefinitionNode|Type|InputObjectField|FieldDefinition|Argument|string $node,
    ): bool {
        return in_array($this->getTypeName($node), Type::STANDARD_TYPE_NAMES, true);
    }

    public function isPlaceholder(
        (TypeDefinitionNode&Node)|(TypeNode&Node)|FieldDefinitionNode|InputValueDefinitionNode|Type|InputObjectField|FieldDefinition|Argument|string $node,
    ): bool {
        // Lighthouse uses `_` type as a placeholder for directives like `@orderBy`
        return $this->getTypeName($node) === static::Placeholder;
    }

    public function isNullable(
        (TypeDefinitionNode&Node)|(TypeNode&Node)|FieldDefinitionNode|InputValueDefinitionNode|Type|InputObjectField|FieldDefinition|Argument $node,
    ): bool {
        $type = null;

        if ($node instanceof InputObjectField || $node instanceof FieldDefinition || $node instanceof Argument) {
            $type = $node->getType();
        } elseif ($node instanceof InputValueDefinitionNode || $node instanceof FieldDefinitionNode) {
            $type = $node->type;
        } elseif ($node instanceof TypeNode || $node instanceof Type) {
            $type = $node;
        } else {
            // empty
        }

        return !($type instanceof NonNull)
            && !($type instanceof NonNullTypeNode);
    }

    public function isList(
        (TypeDefinitionNode&Node)|(TypeNode&Node)|FieldDefinitionNode|InputValueDefinitionNode|Type|InputObjectField|FieldDefinition|Argument $node,
    ): bool {
        $type = null;

        if ($node instanceof InputObjectField || $node instanceof FieldDefinition || $node instanceof Argument) {
            $type = $node->getType();
        } elseif ($node instanceof InputValueDefinitionNode || $node instanceof FieldDefinitionNode) {
            $type = $node->type;
        } elseif ($node instanceof TypeNode || $node instanceof Type) {
            $type = $node;
        } else {
            // empty
        }

        if ($type instanceof NonNull) {
            $type = $type->getWrappedType();
        }

        if ($type instanceof NonNullTypeNode) {
            $type = $type->type;
        }

        return $type instanceof ListOfType
            || $type instanceof ListTypeNode;
    }

    public function isUnion(
        (TypeDefinitionNode&Node)|(TypeNode&Node)|FieldDefinitionNode|InputValueDefinitionNode|Type|InputObjectField|FieldDefinition $node,
    ): bool {
        $type = $this->getType($node);

        return $type instanceof UnionTypeDefinitionNode
            || $type instanceof UnionType;
    }

    public function isObject(
        (TypeDefinitionNode&Node)|(TypeNode&Node)|FieldDefinitionNode|InputValueDefinitionNode|Type|InputObjectField|FieldDefinition $node,
    ): bool {
        $type = $this->getType($node);

        return $type instanceof ObjectTypeDefinitionNode
            || $type instanceof ObjectType
            || $type instanceof InterfaceTypeDefinitionNode
            || $type instanceof InterfaceType
            || $type instanceof InputObjectTypeDefinitionNode
            || $type instanceof InputObjectType;
    }

    public function isScalar(
        (TypeDefinitionNode&Node)|(TypeNode&Node)|FieldDefinitionNode|InputValueDefinitionNode|Type|InputObjectField|FieldDefinition $node,
    ): bool {
        $type = $this->getType($node);

        return $type instanceof ScalarTypeDefinitionNode
            || $type instanceof ScalarType;
    }

    public function isEnum(
        (TypeDefinitionNode&Node)|(TypeNode&Node)|FieldDefinitionNode|InputValueDefinitionNode|Type|InputObjectField|FieldDefinition $node,
    ): bool {
        $type = $this->getType($node);

        return $type instanceof EnumTypeDefinitionNode
            || $type instanceof EnumType;
    }

    public function isDeprecated(
        Node|Argument|EnumValueDefinition|FieldDefinition|InputObjectField $node,
    ): bool {
        $deprecated = false;

        if ($node instanceof Node) {
            $deprecated = $this->getDirective($node, DeprecatedDirective::class) !== null;
        } else {
            $deprecated = $node->deprecationReason !== null;
        }

        return $deprecated;
    }

    public function isTypeDefinitionExists(string $name): bool {
        try {
            return (bool) $this->getTypeDefinition($name);
        } catch (TypeDefinitionUnknown) {
            return false;
        }
    }

    public function getTypeDefinition(
        (TypeDefinitionNode&Node)|(TypeNode&Node)|FieldDefinitionNode|InputValueDefinitionNode|Type|InputObjectField|FieldDefinition|Argument|string $node,
    ): (TypeDefinitionNode&Node)|Type {
        if ($node instanceof TypeDefinitionNode && $node instanceof Node) {
            return $node;
        }

        $name       = $this->getTypeName($node);
        $types      = $this->getTypes();
        $definition = $this->getDocument()->types[$name] ?? null;

        if (!$definition) {
            $definition = Type::getStandardTypes()[$name] ?? null;
        }

        if (!$definition && $types->has($name)) {
            $definition = $types->get($name);
        }

        if (!$definition) {
            throw new TypeDefinitionUnknown($name);
        }

        return $definition;
    }

    /**
     * @template TDefinition of (TypeDefinitionNode&Node)|TypeReference
     *
     * @param TDefinition $definition
     *
     * @return TDefinition
     */
    public function addTypeDefinition(
        (TypeDefinitionNode&Node)|TypeReference $definition,
    ): (TypeDefinitionNode&Node)|TypeReference {
        $name = $this->getName($definition);

        if ($this->isTypeDefinitionExists($name)) {
            throw new TypeDefinitionAlreadyDefined($name);
        }

        if ($definition instanceof TypeDefinitionNode && $definition instanceof Node) {
            $this->getDocument()->setTypeDefinition($definition);
        } elseif ($definition instanceof TypeReference) {
            $directive = DirectiveLocator::directiveName(TypeDirective::class);
            $class     = Cast::to(Node::class, AST::astFromValue($definition->type, Type::string()));
            $class     = Printer::doPrint($class);
            $node      = Parser::scalarTypeDefinition(
                <<<GRAPHQL
                scalar {$name} @{$directive}(class: {$class})
                GRAPHQL,
            );

            $this->getDocument()->setTypeDefinition($node);
        } else {
            // Types added while AST transformation will be lost if the Schema
            // is cached. Not yet sure how to solve it... Any ideas?
            throw new NotImplemented('`Type` registration');
        }

        return $definition;
    }

    public function removeTypeDefinition(string $name): void {
        if (!$this->isTypeDefinitionExists($name)) {
            throw new TypeDefinitionUnknown($name);
        }

        // Remove
        unset($this->getDocument()->types[$name]);
    }

    public function getOriginType(
        FieldDefinitionNode|FieldDefinition|InputValueDefinitionNode|InputObjectField $field,
    ): (TypeNode&Node)|Type {
        $directive = $this->getDirective($field, Directive::class, static function (Directive $directive): bool {
            return $directive instanceof StreamDirective
                || $directive instanceof PaginateDirective
                || $directive instanceof RelationDirective;
        });
        $origin    = $field instanceof FieldDefinition || $field instanceof InputObjectField
            ? $field->getType()
            : $field->type;
        $name      = $this->getTypeName($origin);
        $type      = null;

        if ($directive instanceof StreamDirective) {
            $type = Str::singular(mb_substr($name, 0, -mb_strlen(StreamDirective::Name)));
        } elseif ($directive instanceof PaginateDirective || $directive instanceof RelationDirective) {
            $pagination = $directive instanceof PaginateDirective
                ? PaginateDirectiveHelper::getPaginationType($directive)
                : RelationDirectiveHelper::getPaginationType($directive);

            if ($pagination !== null) {
                if ($pagination->isPaginator()) {
                    $type = mb_substr($name, 0, -mb_strlen('Paginator'));
                } elseif ($pagination->isSimple()) {
                    $type = mb_substr($name, 0, -mb_strlen('SimplePaginator'));
                } elseif ($pagination->isConnection()) {
                    $type = mb_substr($name, 0, -mb_strlen('Connection'));
                } else {
                    // empty
                }
            }
        } else {
            // empty
        }

        if ($type !== null) {
            $origin = Parser::typeReference("[{$type}!]!");
        }

        return $origin;
    }

    /**
     * @template T
     *
     * @param class-string<T>       $class
     * @param Closure(T): bool|null $callback
     *
     * @return (T&Directive)|null
     */
    public function getDirective(
        Node|Type|InputObjectField|FieldDefinition|Argument $node,
        string $class,
        ?Closure $callback = null,
    ): ?Directive {
        // todo(graphql): Seems there is no way to attach directive to \GraphQL\Type\Definition\Type?
        // todo(graphql): Should we throw an error if $node has multiple directives?
        $directives = $this->getDirectives($node);
        $found      = null;

        foreach ($directives as $directive) {
            // Class?
            if (!($directive instanceof $class)) {
                continue;
            }

            // Callback?
            if ($callback !== null && !$callback($directive)) {
                continue;
            }

            // Ok
            $found = $directive;
            break;
        }

        return $found;
    }

    /**
     * @template T
     *
     * @param class-string<T>|null                                           $class
     * @param Closure(($class is null ? Directive : T&Directive)): bool|null $callback
     *
     * @return ($class is null ? array<array-key, Directive> : array<array-key, T&Directive>)
     */
    public function getDirectives(
        Node|Type|InputObjectField|FieldDefinition|Argument $node,
        ?string $class = null,
        ?Closure $callback = null,
    ): array {
        $directives = [];

        if ($node instanceof NamedType) {
            $astNode = $node->astNode();

            if ($astNode !== null) {
                $directives = $this->getDirectives($astNode, $class, $callback);
            }
        } elseif ($node instanceof Node) {
            $associated = $this->getDirectiveLocator()->associated($node);

            if ($class !== null || $callback !== null) {
                foreach ($associated as $directive) {
                    // Class?
                    if ($class !== null && !($directive instanceof $class)) {
                        continue;
                    }

                    // Callback?
                    if ($callback !== null && !$callback($directive)) {
                        continue;
                    }

                    // Ok
                    $directives[] = $directive;
                }
            } else {
                $directives = $associated->all();
            }
        } elseif ($node instanceof InputObjectField || $node instanceof FieldDefinition || $node instanceof Argument) {
            if ($node->astNode !== null) {
                $directives = $this->getDirectives($node->astNode, $class, $callback);
            }
        } else {
            // empty
        }

        return $directives;
    }

    public function getTypeName(
        (TypeDefinitionNode&Node)|(TypeNode&Node)|FieldDefinitionNode|InputValueDefinitionNode|Type|InputObjectField|FieldDefinition|Argument|string $node,
    ): string {
        $name = null;

        if (
            $node instanceof Type
            || $node instanceof InputObjectField
            || $node instanceof FieldDefinition
            || $node instanceof Argument
        ) {
            $type = $node instanceof Type ? $node : $node->getType();

            if ($type instanceof WrappingType) {
                $type = $type->getInnermostType();
            }

            if ($type instanceof NamedType) {
                $name = $type->name();
            }
        } elseif ($node instanceof TypeDefinitionNode) {
            $name = $this->getName($node);
        } elseif ($node instanceof Node) {
            $name = ASTHelper::getUnderlyingTypeName($node);
        } else {
            $name = $node;
        }

        assert($name !== null);

        return $name;
    }

    public function getName(
        InputValueDefinitionNode|(TypeDefinitionNode&Node)|FieldDefinitionNode|InputObjectField|FieldDefinition|Argument|ArgumentNode|Type|TypeReference $node,
    ): string {
        if ($node instanceof TypeDefinitionNode) {
            $node = $node->getName();
        } elseif (
            $node instanceof InputValueDefinitionNode
            || $node instanceof FieldDefinitionNode
            || $node instanceof ArgumentNode
            || $node instanceof TypeReference
        ) {
            $node = $node->name;
        } else {
            // empty
        }

        $name = null;

        if ($node instanceof NameNode) {
            $name = $node->value;
        } elseif ($node instanceof InputObjectField || $node instanceof FieldDefinition || $node instanceof Argument) {
            $name = $node->name;
        } else {
            $name = $this->getTypeName($node);
        }

        return $name;
    }

    public function getTypeFullName(
        (TypeDefinitionNode&Node)|(TypeNode&Node)|FieldDefinitionNode|InputValueDefinitionNode|Type|InputObjectField|FieldDefinition|string $node,
    ): string {
        $name   = $this->getTypeName($node);
        $prefix = null;

        if ($node instanceof WrappingType) {
            $node = $node->getInnermostType();
        }

        if (!($node instanceof Type || $node instanceof TypeDefinitionNode)) {
            $node = $this->getTypeDefinition($name);
        }

        if ($node instanceof InputObjectTypeDefinitionNode || $node instanceof InputObjectType) {
            $prefix = 'input';
        } elseif ($node instanceof ObjectTypeDefinitionNode || $node instanceof ObjectType) {
            $prefix = 'type';
        } elseif ($node instanceof InterfaceTypeDefinitionNode || $node instanceof InterfaceType) {
            $prefix = 'interface';
        } elseif ($node instanceof ScalarTypeDefinitionNode || $node instanceof ScalarType) {
            $prefix = 'scalar';
        } elseif ($node instanceof EnumTypeDefinitionNode || $node instanceof EnumType) {
            $prefix = 'enum';
        } elseif ($node instanceof UnionTypeDefinitionNode || $node instanceof UnionType) {
            $prefix = 'union';
        } else {
            // empty
        }

        return trim("{$prefix} {$name}");
    }

    /**
     * @return array<string, InterfaceTypeDefinitionNode|InterfaceType>
     */
    public function getInterfaces(
        ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode|ObjectType|InterfaceType $node,
    ): array {
        $interfaces     = [];
        $nodeInterfaces = $node instanceof Type
            ? $node->getInterfaces()
            : $node->interfaces;

        foreach ($nodeInterfaces as $interface) {
            $name = $this->getTypeName($interface);

            if ($interface instanceof NamedTypeNode) {
                $interface = $this->getTypeDefinition($interface);
            }

            if ($interface instanceof InterfaceTypeDefinitionNode || $interface instanceof InterfaceType) {
                $interfaces = array_merge(
                    $interfaces,
                    [
                        $name => $interface,
                    ],
                    $this->getInterfaces($interface),
                );
            }
        }

        return $interfaces;
    }

    /**
     * @param Closure(FieldDefinitionNode|FieldDefinition): bool $closure
     */
    public function findField(
        InterfaceTypeDefinitionNode|ObjectTypeDefinitionNode|HasFieldsType $node,
        Closure $closure,
    ): FieldDefinitionNode|FieldDefinition|null {
        $found  = null;
        $fields = $node instanceof HasFieldsType
            ? $node->getFields()
            : $node->fields;

        foreach ($fields as $field) {
            if ($closure($field)) {
                $found = $field;
                break;
            }
        }

        return $found;
    }

    /**
     * @return ($node is HasFieldsType ? FieldDefinition : FieldDefinitionNode)|null
     */
    public function getField(
        InterfaceTypeDefinitionNode|ObjectTypeDefinitionNode|HasFieldsType $node,
        string $name,
    ): FieldDefinitionNode|FieldDefinition|null {
        $field = null;

        if ($node instanceof HasFieldsType) {
            $field = $node->hasField($name) ? $node->getField($name) : null;
        } else {
            $field = $this->findField($node, function (mixed $field) use ($name): bool {
                return $this->getName($field) === $name;
            });
        }

        return $field;
    }

    /**
     * @param callable(InputValueDefinitionNode|Argument|ArgumentNode): bool $closure
     *
     * @return array<string, ($node is FieldDefinitionNode ? InputValueDefinitionNode : ($node is FieldDefinition ? Argument : ArgumentNode))>
     */
    public function findArguments(
        FieldDefinitionNode|FieldDefinition|DirectiveNode $node,
        callable $closure,
    ): array {
        $arguments = [];
        $args      = $node instanceof FieldDefinitionNode || $node instanceof DirectiveNode
            ? $node->arguments
            : $node->args;

        foreach ($args as $arg) {
            if ($closure($arg)) {
                $arguments[$this->getName($arg)] = $arg;
            }
        }

        return $arguments;
    }

    /**
     * @param callable(InputValueDefinitionNode|Argument|ArgumentNode): bool $closure
     *
     * @return ($node is FieldDefinitionNode ? InputValueDefinitionNode : ($node is FieldDefinition ? Argument : ArgumentNode))|null
     */
    public function findArgument(
        FieldDefinitionNode|FieldDefinition|DirectiveNode $node,
        callable $closure,
    ): InputValueDefinitionNode|Argument|ArgumentNode|null {
        $argument = null;
        $args     = $node instanceof FieldDefinitionNode || $node instanceof DirectiveNode
            ? $node->arguments
            : $node->args;

        foreach ($args as $arg) {
            if ($closure($arg)) {
                $argument = $arg;
                break;
            }
        }

        return $argument;
    }

    /**
     * @return ($node is FieldDefinitionNode ? InputValueDefinitionNode : ($node is FieldDefinition ? Argument : ArgumentNode))|null
     */
    public function getArgument(
        FieldDefinitionNode|FieldDefinition|DirectiveNode $node,
        string $name,
    ): InputValueDefinitionNode|Argument|ArgumentNode|null {
        return $this->findArgument(
            $node,
            function (mixed $argument) use ($name): bool {
                return $this->getName($argument) === $name;
            },
        );
    }

    public function getDirectiveNode(Directive|DirectiveNode $directive): ?DirectiveNode {
        $node = null;

        if ($directive instanceof BaseDirective) {
            $node = $directive->directiveNode;
        } elseif ($directive instanceof DirectiveNode) {
            $node = $directive;
        } else {
            // empty
        }

        return $node;
    }

    public function addArgument(
        ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode|ObjectType|InterfaceType $definition,
        FieldDefinitionNode|FieldDefinition $field,
        string $name,
        string $type,
        mixed $default = null,
        ?string $description = null,
    ): InputValueDefinitionNode|Argument {
        // Added?
        if ($this->getArgument($field, $name) !== null) {
            throw new ArgumentAlreadyDefined(
                sprintf(
                    '%s { %s(%s) }',
                    $this->getTypeFullName($definition),
                    $this->getName($field),
                    $name,
                ),
            );
        }

        // Add
        if ($field instanceof FieldDefinitionNode) {
            $argument = ''
                .($description !== null && $description !== '' ? BlockString::print($description) : '')
                ."{$name}: {$type}"
                .($default !== null ? ' = '.json_encode($default, JSON_THROW_ON_ERROR) : '');
            $argument = Parser::inputValueDefinition($argument);

            $field->arguments[] = $argument;
        } else {
            $argument = new Argument([
                'name'         => $name,
                'type'         => $this->toType($type, InputType::class),
                'description'  => $description,
                'defaultValue' => $default,
            ]);

            $field->args[] = $argument;
        }

        // Interfaces
        // (to make sure to get a valid schema)
        $interfaces = $this->getInterfaces($definition);
        $fieldName  = $this->getName($field);

        foreach ($interfaces as $interface) {
            // Field?
            $interfaceField = $this->getField($interface, $fieldName);

            if ($interfaceField === null) {
                continue;
            }

            // Update
            $this->addArgument(
                $interface,
                $interfaceField,
                $name,
                $type,
                $default,
                $description,
            );
        }

        // Return
        return $argument;
    }

    /**
     * @param class-string<Directive> $directive
     * @param array<string, mixed>    $arguments
     */
    public function addDirective(
        FieldDefinitionNode|InputValueDefinitionNode|Argument $node,
        string $directive,
        array $arguments = [],
    ): DirectiveNode {
        // Not a Node?
        if ($node instanceof Argument) {
            // Unfortunately directives exists only in AST :(
            // https://github.com/webonyx/graphql-php/issues/588
            if ($node->astNode !== null) {
                return $this->addDirective($node->astNode, $directive, $arguments);
            } else {
                throw new NotImplemented($node::class);
            }
        }

        // Add
        $name               = DirectiveLocator::directiveName($directive);
        $definition         = Parser::directive("@{$name}");
        $node->directives[] = $definition;

        foreach ($arguments as $argument => $value) {
            $definition->arguments[] = Parser::argument($argument.': '.json_encode($value, JSON_THROW_ON_ERROR));
        }

        return $definition;
    }

    /**
     * @template T of FieldDefinitionNode|FieldDefinition
     *
     * @param T $field
     *
     * @return T
     */
    public function setFieldType(
        ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode|ObjectType|InterfaceType $definition,
        FieldDefinitionNode|FieldDefinition $field,
        (Type&InputType)|NamedTypeNode|ListTypeNode|NonNullTypeNode $type,
    ): FieldDefinitionNode|FieldDefinition {
        // Update
        $this->setType($field, $type);

        // Interfaces
        $interfaces = $this->getInterfaces($definition);
        $fieldName  = $this->getName($field);

        foreach ($interfaces as $interface) {
            // Field?
            $interfaceField = $this->getField($interface, $fieldName);

            if ($interfaceField === null) {
                continue;
            }

            // Update
            $this->setType($interfaceField, $type);
        }

        // Return
        return $field;
    }

    /**
     * @template T of InputValueDefinitionNode|Argument
     *
     * @param T $argument
     *
     * @return T
     */
    public function setArgumentType(
        ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode|ObjectType|InterfaceType $definition,
        FieldDefinitionNode|FieldDefinition $field,
        InputValueDefinitionNode|Argument $argument,
        (Type&InputType)|NamedTypeNode|ListTypeNode|NonNullTypeNode $type,
    ): InputValueDefinitionNode|Argument {
        // Update
        $this->setType($argument, $type);

        // Interfaces
        $interfaces   = $this->getInterfaces($definition);
        $fieldName    = $this->getName($field);
        $argumentName = $this->getName($argument);

        foreach ($interfaces as $interface) {
            // Field?
            $interfaceField = $this->getField($interface, $fieldName);

            if ($interfaceField === null) {
                continue;
            }

            // Argument?
            $interfaceArgument = $this->getArgument($interfaceField, $argumentName);

            if ($interfaceArgument === null) {
                continue;
            }

            // Update
            $this->setType($interfaceArgument, $type);
        }

        // Return
        return $argument;
    }

    /**
     * @template T
     *
     * @param class-string<T> $expected
     *
     * @return Type&T
     */
    private function toType((TypeNode&Node)|string $name, string $expected): Type {
        // todo(graphql): Is there a better way to get Type?
        $type = null;
        $node = is_string($name) ? Parser::typeReference($name) : $name;

        if ($node instanceof ListTypeNode) {
            $type = Type::listOf($this->toType($node->type, Type::class));
        } elseif ($node instanceof NonNullTypeNode) {
            $type = Type::nonNull($this->toType($node->type, NullableType::class));
        } else {
            $type = $this->getTypeDefinition($node);

            if ($type instanceof Node) {
                $type = $this->getTypes()->handle($type);
            }
        }

        if (!($type instanceof $expected)) {
            throw new TypeUnexpected($name, $expected);
        }

        return $type;
    }

    private function setType(
        FieldDefinitionNode|FieldDefinition|InputValueDefinitionNode|Argument $node,
        (Type&InputType)|NamedTypeNode|ListTypeNode|NonNullTypeNode $type,
    ): void {
        // It seems that we can only modify types of AST nodes :(
        if ($node instanceof Node) {
            $node->type = !($type instanceof Node)
                ? Parser::typeReference($type->toString())
                : $type;
        } else {
            throw new NotImplemented($node::class);
        }
    }

    private function getType(
        (TypeDefinitionNode&Node)|(TypeNode&Node)|FieldDefinitionNode|InputValueDefinitionNode|Type|InputObjectField|FieldDefinition $node,
    ): (Node&TypeDefinitionNode)|Type|null {
        if ($node instanceof InputObjectField || $node instanceof FieldDefinition) {
            $node = $node->getType();
        } elseif ($node instanceof Node) {
            try {
                $node = $this->getTypeDefinition($node);
            } catch (TypeDefinitionUnknown) {
                $node = null;
            }
        } else {
            // empty
        }

        if ($node instanceof WrappingType) {
            $node = $node->getInnermostType();
        }

        return $node;
    }
    // </editor-fold>
}
