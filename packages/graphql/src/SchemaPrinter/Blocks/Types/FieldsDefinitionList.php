<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types;

use GraphQL\Type\Definition\FieldDefinition;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\BlockList;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;
use Traversable;

/**
 * @internal
 * @extends BlockList<FieldDefinitionBlock>
 */
class FieldsDefinitionList extends BlockList {
    /**
     * @param Traversable<FieldDefinition>|array<FieldDefinition> $fields
     */
    public function __construct(
        Dispatcher $dispatcher,
        Settings $settings,
        int $level,
        int $used,
        Traversable|array $fields,
    ) {
        parent::__construct($dispatcher, $settings, $level, $used);

        foreach ($fields as $field) {
            $this[$field->name] = new FieldDefinitionBlock(
                $this->getDispatcher(),
                $this->getSettings(),
                $this->getLevel() + 1,
                $this->getUsed(),
                $field,
            );
        }
    }

    protected function getPrefix(): string {
        return '{';
    }

    protected function getSuffix(): string {
        return '}';
    }

    protected function isWrapped(): bool {
        return true;
    }

    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeFields();
    }

    protected function isAlwaysMultiline(): bool {
        return true;
    }
}
