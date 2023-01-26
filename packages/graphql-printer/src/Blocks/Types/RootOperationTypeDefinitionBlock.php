<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types;

use GraphQL\Type\Definition\ObjectType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\PrinterSettings;

/**
 * @internal
 */
class RootOperationTypeDefinitionBlock extends TypeBlock {
    public function __construct(
        PrinterSettings $settings,
        int $level,
        int $used,
        private OperationType $operation,
        ObjectType $type,
    ) {
        parent::__construct($settings, $level, $used, $type);
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
