<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types;

use GraphQL\Type\Definition\ObjectType;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;

/**
 * @internal
 */
class RootOperationTypeDefinitionBlock extends TypeBlock {
    public function __construct(
        Dispatcher $dispatcher,
        Settings $settings,
        int $level,
        int $used,
        private OperationType $operation,
        ObjectType $type,
    ) {
        parent::__construct($dispatcher, $settings, $level, $used, $type);
    }

    public function getOperation(): OperationType {
        return $this->operation;
    }

    protected function content(): string {
        $content = parent::content();
        $content = "{$this->getOperation()}:{$this->space()}{$content}";

        return $content;
    }
}
