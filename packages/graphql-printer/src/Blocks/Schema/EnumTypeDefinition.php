<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Schema;

use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\PhpEnumType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\DefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLDefinition;

use function mb_strlen;

/**
 * @internal
 *
 * @extends DefinitionBlock<EnumType>
 */
#[GraphQLDefinition(EnumType::class)]
#[GraphQLDefinition(PhpEnumType::class)]
class EnumTypeDefinition extends DefinitionBlock {
    public function __construct(
        Context $context,
        int $level,
        int $used,
        EnumType $definition,
    ) {
        parent::__construct($context, $level, $used, $definition);
    }

    protected function type(): string {
        return 'enum';
    }

    protected function body(int $used): Block|string|null {
        return null;
    }

    protected function fields(int $used): Block|string|null {
        $space  = $this->space();
        $values = $this->addUsed(
            new EnumValuesDefinition(
                $this->getContext(),
                $this->getLevel(),
                $used + mb_strlen($space),
                $this->getDefinition()->getValues(),
            ),
        );

        return $values;
    }
}
