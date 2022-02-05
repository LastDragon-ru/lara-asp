<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types;

use GraphQL\Type\Definition\InputObjectField;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\BlockList;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Misc\PrinterSettings;
use Traversable;

/**
 * @internal
 * @extends BlockList<InputValueDefinitionBlock>
 */
class InputFieldsDefinitionList extends BlockList {
    /**
     * @param Traversable<InputObjectField>|array<InputObjectField> $fields
     */
    public function __construct(
        PrinterSettings $settings,
        int $level,
        int $used,
        Traversable|array $fields,
    ) {
        parent::__construct($settings, $level, $used);

        foreach ($fields as $field) {
            $this[$field->name] = new InputValueDefinitionBlock(
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