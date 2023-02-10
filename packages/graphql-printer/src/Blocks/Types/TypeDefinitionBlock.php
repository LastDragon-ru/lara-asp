<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types;

use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Schema\FieldsDefinition;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Schema\ImplementsInterfaces;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;

use function mb_strlen;

/**
 * @internal
 *
 * @template TType of InterfaceType|ObjectType
 *
 * @extends DefinitionBlock<TType>
 */
abstract class TypeDefinitionBlock extends DefinitionBlock {
    /**
     * @param TType $definition
     */
    public function __construct(
        Settings $settings,
        int $level,
        int $used,
        InterfaceType|ObjectType $definition,
    ) {
        parent::__construct($settings, $level, $used, $definition);
    }

    protected function body(int $used): Block|string|null {
        $definition = $this->getDefinition();
        $space      = $this->space();
        $interfaces = $this->addUsed(
            new ImplementsInterfaces(
                $this->getSettings(),
                $this->getLevel() + 1,
                $used + mb_strlen($space),
                $definition->getInterfaces(),
            ),
        );

        if (!$interfaces->isEmpty()) {
            if ($interfaces->isMultiline()) {
                $eol        = $this->eol();
                $indent     = $this->indent($this->getLevel());
                $interfaces = "{$eol}{$indent}{$interfaces}";
            } else {
                $interfaces = "{$space}{$interfaces}";
            }
        }

        return $interfaces;
    }

    protected function fields(int $used): Block|string|null {
        $definition = $this->getDefinition();
        $space      = $this->space();
        $fields     = new FieldsDefinition(
            $this->getSettings(),
            $this->getLevel(),
            $used + mb_strlen($space),
            $definition->getFields(),
        );

        return $this->addUsed($fields);
    }
}
