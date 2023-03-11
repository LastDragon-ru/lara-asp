<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Types;

use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Parser;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;

class Range implements TypeDefinition {
    public function __construct() {
        // empty
    }

    public static function getTypeName(Manipulator $manipulator, BuilderInfo $builder, TypeSource $source): string {
        $typeName      = $source->getTypeName();
        $directiveName = Directive::Name;

        return "{$directiveName}TypeRange{$typeName}";
    }

    public function getTypeDefinitionNode(
        Manipulator $manipulator,
        string $name,
        TypeSource $source,
    ): ?TypeDefinitionNode {
        return Parser::inputObjectTypeDefinition(
        /** @lang GraphQL */
            <<<GRAPHQL
            input {$name} {
                min: {$source->getTypeName()}!
                max: {$source->getTypeName()}!
            }
            GRAPHQL,
        );
    }
}
