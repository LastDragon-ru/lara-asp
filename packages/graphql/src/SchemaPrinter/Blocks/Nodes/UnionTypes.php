<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Nodes;

use GraphQL\Type\Definition\ObjectType;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\BlockList;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;
use Traversable;

/**
 * @internal
 * @extends BlockList<TypeName>
 */
class UnionTypes extends BlockList {
    /**
     * @param Traversable<ObjectType>|array<ObjectType> $types
     */
    public function __construct(
        Dispatcher $dispatcher,
        Settings $settings,
        int $level,
        int $used,
        Traversable|array $types,
    ) {
        parent::__construct($dispatcher, $settings, $level, $used);

        foreach ($types as $type) {
            $this[$type->name] = new TypeName(
                $this->getDispatcher(),
                $this->getSettings(),
                $this->getLevel() + 1,
                $this->getUsed(),
                $type,
            );
        }
    }

    protected function getSeparator(): string {
        return "{$this->space()}|{$this->space()}";
    }

    protected function getMultilineSeparator(): string {
        return "|{$this->space()}";
    }

    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeUnions();
    }

    protected function isBlock(): bool {
        return false;
    }
}
