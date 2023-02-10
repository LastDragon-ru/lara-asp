<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Schema;

use GraphQL\Type\Definition\Type as GraphQLType;
use GraphQL\Type\Definition\WrappingType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\NamedBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLDefinition;

/**
 * @internal
 */
#[GraphQLDefinition(GraphQLType::class)]
class Type extends Block implements NamedBlock {
    public function __construct(
        Settings $settings,
        int $level,
        int $used,
        private GraphQLType $definition,
    ) {
        parent::__construct($settings, $level, $used);
    }

    public function getName(): string {
        $type = $this->getType();

        if ($type instanceof WrappingType) {
            $type = $type->getWrappedType(true);
        }

        return $type->name;
    }

    protected function getType(): GraphQLType {
        return $this->definition;
    }

    protected function content(): string {
        $this->addUsedType($this->getName());

        return (string) $this->getType();
    }
}
