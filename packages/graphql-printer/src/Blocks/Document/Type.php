<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\Type as GraphQLType;
use GraphQL\Type\Definition\WrappingType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\NamedBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLDefinition;

use function assert;

/**
 * @internal
 */
#[GraphQLDefinition(ListOfType::class)]
#[GraphQLDefinition(NonNull::class)]
class Type extends Block implements NamedBlock {
    public function __construct(
        Context $context,
        int $level,
        int $used,
        private GraphQLType $definition,
    ) {
        parent::__construct($context, $level, $used);
    }

    public function getName(): string {
        $name = null;
        $type = $this->getType();

        if ($type instanceof WrappingType) {
            $type = $type->getInnermostType();
        }

        if ($type instanceof NamedType) {
            $name = $type->name();
        }

        assert($name !== null);

        return $name;
    }

    protected function getType(): GraphQLType {
        return $this->definition;
    }

    protected function content(): string {
        $this->addUsedType($this->getName());

        return (string) $this->getType();
    }
}
