<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Type\Definition\ObjectType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\TypeDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLDefinition;

/**
 * @internal
 *
 * @extends TypeDefinitionBlock<ObjectType>
 */
#[GraphQLDefinition(ObjectType::class)]
class ObjectTypeDefinition extends TypeDefinitionBlock {
    public function __construct(
        Context $context,
        int $level,
        int $used,
        ObjectType $definition,
    ) {
        parent::__construct($context, $level, $used, $definition);
    }

    protected function type(): string|null {
        return 'type';
    }
}
