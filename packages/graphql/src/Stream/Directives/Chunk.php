<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Directives;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\Parser;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgManipulator;

use function json_encode;

use const JSON_THROW_ON_ERROR;

class Chunk extends BaseDirective implements ArgManipulator {
    public const ArgDefault = 'default';
    public const ArgMax     = 'max';

    public static function definition(): string {
        $name       = DirectiveLocator::directiveName(static::class);
        $argMax     = self::ArgMax;
        $argDefault = self::ArgDefault;

        return <<<GraphQL
            directive @{$name}(
                {$argDefault}: Int!
                {$argMax}: Int!
            ) on ARGUMENT_DEFINITION
        GraphQL;
    }

    public function manipulateArgDefinition(
        DocumentAST &$documentAST,
        InputValueDefinitionNode &$argDefinition,
        FieldDefinitionNode &$parentField,
        ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode &$parentType,
    ): void {
        $argDefinition->type         = Parser::typeReference('Int');
        $argDefinition->defaultValue = Parser::valueLiteral(
            json_encode($this->directiveArgValue(static::ArgDefault), JSON_THROW_ON_ERROR),
        );

        // fixme(graphql/@stream)!: Validation directives
    }
}
