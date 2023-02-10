<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Definitions;

use GraphQL\Type\Definition\InputObjectType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\DefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLDefinition;

use function mb_strlen;

/**
 * @internal
 *
 * @extends DefinitionBlock<InputObjectType>
 */
#[GraphQLDefinition(InputObjectType::class)]
class InputObjectTypeDefinition extends DefinitionBlock {
    public function __construct(
        Settings $settings,
        int $level,
        int $used,
        InputObjectType $definition,
    ) {
        parent::__construct($settings, $level, $used, $definition);
    }

    protected function type(): string|null {
        return 'input';
    }

    protected function body(int $used): Block|string|null {
        return null;
    }

    protected function fields(int $used): Block|string|null {
        $definition = $this->getDefinition();
        $space      = $this->space();
        $fields     = $this->addUsed(
            new InputFieldsDefinition(
                $this->getSettings(),
                $this->getLevel(),
                $used + mb_strlen($space),
                $definition->getFields(),
            ),
        );

        return $fields;
    }
}
