<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types;

use GraphQL\Type\Definition\EnumType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\PrinterSettings;

use function mb_strlen;

/**
 * @internal
 *
 * @extends DefinitionBlock<EnumType>
 */
class EnumTypeDefinitionBlock extends DefinitionBlock {
    public function __construct(
        PrinterSettings $settings,
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
            new EnumValuesDefinitionList(
                $this->getSettings(),
                $this->getLevel(),
                $used + mb_strlen($space),
                $this->getDefinition()->getValues(),
            ),
        );

        return $values;
    }
}
