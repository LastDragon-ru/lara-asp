<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Nodes;

use GraphQL\Type\Definition\ObjectType;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Events\TypeUsed;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Named;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;

/**
 * @internal
 */
class TypeName extends Block implements Named {
    public function __construct(
        Dispatcher $dispatcher,
        Settings $settings,
        int $level,
        int $used,
        private ObjectType $type,
    ) {
        parent::__construct($dispatcher, $settings, $level, $used);
    }

    public function getName(): string {
        return $this->getType()->name;
    }

    protected function getType(): ObjectType {
        return $this->type;
    }

    protected function content(): string {
        $this->getDispatcher()->notify(
            new TypeUsed($this->getName()),
        );

        return $this->getName();
    }
}
