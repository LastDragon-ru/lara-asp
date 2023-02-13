<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Schema;

use GraphQL\Type\Definition\FieldDefinition as GraphQLFieldDefinition;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ListBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use Traversable;

/**
 * @internal
 * @extends ListBlock<FieldDefinition>
 */
class FieldsDefinition extends ListBlock {
    /**
     * @param Traversable<GraphQLFieldDefinition>|array<GraphQLFieldDefinition> $fields
     */
    public function __construct(
        Settings $settings,
        int $level,
        int $used,
        Traversable|array $fields,
    ) {
        parent::__construct($settings, $level, $used);

        foreach ($fields as $field) {
            $this[$field->name] = new FieldDefinition(
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
