<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Nodes;

use GraphQL\Type\Definition\EnumType;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;

use function mb_strlen;

/**
 * @internal
 *
 * @extends TypeBlock<EnumType>
 */
class Enum extends TypeBlock {
    public function __construct(
        Dispatcher $dispatcher,
        Settings $settings,
        int $level,
        int $used,
        EnumType $type,
    ) {
        parent::__construct($dispatcher, $settings, $level, $used, $type);
    }

    protected function body(int $used): string {
        $space  = $this->space();
        $body   = "enum{$space}{$this->getName()}{$space}";
        $values = new EnumValues(
            $this->getDispatcher(),
            $this->getSettings(),
            $this->getLevel(),
            $used + mb_strlen($body),
            $this->getType()->getValues(),
        );

        return "{$body}{$values}";
    }
}
