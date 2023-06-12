<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Type\Definition\InputObjectType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\DefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
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
        Context $context,
        int $level,
        int $used,
        InputObjectType $definition,
    ) {
        parent::__construct($context, $level, $used, $definition);
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
                $this->getContext(),
                $this->getLevel(),
                $used + mb_strlen($space),
                $definition->getFields(),
            ),
        );

        return $fields;
    }
}
