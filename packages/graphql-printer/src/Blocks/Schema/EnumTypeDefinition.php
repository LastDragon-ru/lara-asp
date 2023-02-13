<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Schema;

use GraphQL\Type\Definition\EnumType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\DefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLDefinition;

use function mb_strlen;

/**
 * @internal
 *
 * @extends DefinitionBlock<EnumType>
 */
#[GraphQLDefinition(EnumType::class)]
class EnumTypeDefinition extends DefinitionBlock {
    public function __construct(
        Settings $settings,
        int $level,
        int $used,
        EnumType $definition,
    ) {
        parent::__construct($settings, $level, $used, $definition);
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
                $this->getSettings(),
                $this->getLevel(),
                $used + mb_strlen($space),
                $this->getDefinition()->getValues(),
            ),
        );

        return $values;
    }
}
