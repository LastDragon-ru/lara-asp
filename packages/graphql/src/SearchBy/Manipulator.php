<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy;

use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\InputObjectType;
use Illuminate\Contracts\Container\Container;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator as BuilderManipulator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\FailedToCreateSearchCondition;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Types\Condition;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\TypeRegistry;

use function str_starts_with;

class Manipulator extends BuilderManipulator {
    public function __construct(
        DirectiveLocator $directives,
        DocumentAST $document,
        TypeRegistry $types,
        Container $container,
        BuilderInfo $builderInfo,
        Operators $operators,
    ) {
        parent::__construct($directives, $document, $types, $container, $builderInfo, $operators);
    }

    // <editor-fold desc="Update">
    // =========================================================================
    public function update(DirectiveNode $directive, InputValueDefinitionNode $node): void {
        $def  = $this->getTypeDefinitionNode($node);
        $type = null;

        if ($def instanceof InputObjectTypeDefinitionNode || $def instanceof InputObjectType) {
            $name = $this->getNodeTypeName($def);

            if (!str_starts_with($name, Directive::Name)) {
                $name = $this->getType(Condition::class, $name, $this->isNullable($node));
                $type = Parser::typeReference($name);
            } else {
                $type = $node->type;
            }
        }

        if (!($type instanceof NamedTypeNode)) {
            throw new FailedToCreateSearchCondition($this->getNodeName($node));
        }

        // Update
        $node->type = $type;
    }
    // </editor-fold>
}
