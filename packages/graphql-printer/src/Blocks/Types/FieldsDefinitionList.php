<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types;

use GraphQL\Type\Definition\FieldDefinition;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ListBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use Traversable;

/**
 * @internal
 * @extends ListBlock<FieldDefinitionBlock>
 */
class FieldsDefinitionList extends ListBlock {
    /**
     * @param Traversable<FieldDefinition>|array<FieldDefinition> $fields
     */
    public function __construct(
        Settings $settings,
        int $level,
        int $used,
        Traversable|array $fields,
    ) {
        parent::__construct($settings, $level, $used);

        foreach ($fields as $field) {
            $this[$field->name] = new FieldDefinitionBlock(
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
