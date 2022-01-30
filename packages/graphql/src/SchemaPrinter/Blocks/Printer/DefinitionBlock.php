<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Printer;

use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnionType;
use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\BlockSettings;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Named;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types\DirectiveDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types\EnumTypeDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types\InputObjectTypeDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types\InterfaceTypeDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types\ObjectTypeDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types\ScalarTypeDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types\SchemaDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types\UnionTypeDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Exceptions\TypeUnsupported;

/**
 * @internal
 */
class DefinitionBlock extends Block implements Named {
    private Block $block;

    public function __construct(
        BlockSettings $settings,
        int $level,
        Schema|Type|Directive $definition,
    ) {
        parent::__construct($settings, $level);

        $this->block = $this->getDefinitionBlock($definition);
    }

    public function getName(): string {
        $name  = '';
        $block = $this->getBlock();

        if ($block instanceof Named) {
            $name = $block->getName();
        }

        return $name;
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
