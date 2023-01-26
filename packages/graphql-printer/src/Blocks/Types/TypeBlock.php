<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\WrappingType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\NamedBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;

/**
 * @internal
 */
class TypeBlock extends Block implements NamedBlock {
    public function __construct(
        Settings $settings,
        int $level,
        int $used,
        private Type $definition,
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

    protected function getType(): Type {
        return $this->definition;
    }

    protected function content(): string {
        $this->addUsedType($this->getName());

        return (string) $this->getType();
    }
}
