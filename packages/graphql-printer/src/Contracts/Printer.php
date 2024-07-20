<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Contracts;

use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Type\Definition\Argument;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\EnumValueDefinition;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;

interface Printer {
    /**
     * Print the current type/note/etc only.
     *
     * Please note:
     * - types filtering will work only if the schema is known
     * - for some AST node types, their type may also be required
     *
     * @see self::setSchema()
     */
    public function print(
        Node|Type|Directive|FieldDefinition|Argument|EnumValueDefinition|InputObjectField|Schema $printable,
        int $level = 0,
        int $used = 0,
        (TypeNode&Node)|Type|null $type = null,
    ): Result;

    /**
     * Print current type/note/etc and all used types/directives.
     *
     * Please note:
     * - the exported object will be on the top of document
     * - the document may be not valid GraphQL document for some objects (fields/arguments/etc)
     * - the Schema is required to determine the type (and used types) of argument/variable/etc nodes
     * - types filtering will work only if the schema is known
     * - for some AST node types, their type may also be required
     *
     * @see self::setSchema()
     */
    public function export(
        Node|Type|Directive|FieldDefinition|Argument|EnumValueDefinition|InputObjectField|Schema $printable,
        int $level = 0,
        int $used = 0,
        (TypeNode&Node)|Type|null $type = null,
    ): Result;

    public function getSettings(): Settings;

    public function setSettings(?Settings $settings): static;

    public function getDirectiveResolver(): ?DirectiveResolver;

    public function setDirectiveResolver(?DirectiveResolver $directiveResolver): static;

    public function getSchema(): ?Schema;

    public function setSchema(?Schema $schema): static;
}
