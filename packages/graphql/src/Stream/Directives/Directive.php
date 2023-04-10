<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Directives;

use Exception;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\FieldManipulator;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;

class Directive extends BaseDirective implements FieldResolver, FieldManipulator {
    public const Name = 'Stream';

    public static function definition(): string {
        return <<<'GraphQL'
            """
            Convert Input into Sort Clause.
            """
            directive @stream(
                searchable: Boolean
                sortable: Boolean
                chunk: Int
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
        throw new Exception('Not implemented.');
    }
    // </editor-fold>

    // <editor-fold desc="FieldResolver">
    // =========================================================================
    public function resolveField(FieldValue $fieldValue): callable {
        throw new Exception('Not implemented.');
    }
    // </editor-fold>
}
