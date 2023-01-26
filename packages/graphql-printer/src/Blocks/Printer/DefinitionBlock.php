<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Printer;

use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnionType;
use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\NamedBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\DirectiveDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\EnumTypeDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\InputObjectTypeDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\InterfaceTypeDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\ObjectTypeDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\ScalarTypeDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\SchemaDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\UnionTypeDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Exceptions\TypeUnsupported;

/**
 * @internal
 */
class DefinitionBlock extends Block implements NamedBlock {
    private Block $block;

    public function __construct(
        Settings $settings,
        int $level,
        private Schema|Type|Directive $definition,
    ) {
        parent::__construct($settings, $level);

        $this->block = $this->getDefinitionBlock($definition);
    }

    public function getName(): string {
        $name  = '';
        $block = $this->getBlock();

        if ($block instanceof NamedBlock) {
            $name = $block->getName();
        }

        return $name;
    }

    public function getDefinition(): Type|Schema|Directive {
        return $this->definition;
    }

    protected function getBlock(): Block {
        return $this->block;
    }

    protected function content(): string {
        return (string) $this->addUsed($this->getBlock());
    }

    protected function getDefinitionBlock(Schema|Type|Directive $definition): Block {
        $block = null;

        if ($definition instanceof ObjectType) {
            $block = new ObjectTypeDefinitionBlock(
                $this->getSettings(),
                $this->getLevel(),
                $this->getUsed(),
                $definition,
            );
        } elseif ($definition instanceof InputObjectType) {
            $block = new InputObjectTypeDefinitionBlock(
                $this->getSettings(),
                $this->getLevel(),
                $this->getUsed(),
                $definition,
            );
        } elseif ($definition instanceof ScalarType) {
            $block = new ScalarTypeDefinitionBlock(
                $this->getSettings(),
                $this->getLevel(),
                $this->getUsed(),
                $definition,
            );
        } elseif ($definition instanceof InterfaceType) {
            $block = new InterfaceTypeDefinitionBlock(
                $this->getSettings(),
                $this->getLevel(),
                $this->getUsed(),
                $definition,
            );
        } elseif ($definition instanceof UnionType) {
            $block = new UnionTypeDefinitionBlock(
                $this->getSettings(),
                $this->getLevel(),
                $this->getUsed(),
                $definition,
            );
        } elseif ($definition instanceof EnumType) {
            $block = new EnumTypeDefinitionBlock(
                $this->getSettings(),
                $this->getLevel(),
                $this->getUsed(),
                $definition,
            );
        } elseif ($definition instanceof Directive) {
            $block = new DirectiveDefinitionBlock(
                $this->getSettings(),
                $this->getLevel(),
                $this->getUsed(),
                $definition,
            );
        } elseif ($definition instanceof Schema) {
            $block = new SchemaDefinitionBlock(
                $this->getSettings(),
                $this->getLevel(),
                $this->getUsed(),
                $definition,
            );
        } else {
            throw new TypeUnsupported($definition);
        }

        return $block;
    }
}
