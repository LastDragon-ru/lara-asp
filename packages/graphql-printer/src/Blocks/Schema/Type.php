<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Schema;

use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\Type as GraphQLType;
use GraphQL\Type\Definition\WrappingType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\NamedBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLDefinition;

/**
 * @internal
 */
#[GraphQLDefinition(NamedType::class)]
#[GraphQLDefinition(WrappingType::class)]
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
