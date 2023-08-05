<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Types;

use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Stream\Directives\Directive;

class Info implements TypeDefinition {
    public function __construct() {
        // empty
    }

    public function getTypeName(Manipulator $manipulator, BuilderInfo $builder, TypeSource $source): string {
        return Directive::Name.'Info';
    }

    public function getTypeDefinition(
        Manipulator $manipulator,
        string $name,
        TypeSource $source,
    ): TypeDefinitionNode|Type|null {
        return Parser::objectTypeDefinition(
            <<<GraphQL
            type {$name} {
                chunk: Int!
                where: String! # fixme(graphql/@stream)!: Should be a JSON?
                order: String!  # fixme(graphql/@stream)!: Should be a JSON?
            }
            GraphQL,
        );
    }
}
