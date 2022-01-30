<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types;

use GraphQL\Type\Definition\FieldArgument;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\BlockList;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\BlockSettings;
use Traversable;

/**
 * @internal
 * @extends BlockList<InputValueDefinitionBlock>
 */
class ArgumentsDefinitionList extends BlockList {
    /**
     * @param Traversable<FieldArgument>|array<FieldArgument> $arguments
     */
    public function __construct(
        BlockSettings $settings,
        int $level,
        int $used,
        Traversable|array $arguments,
    ) {
        parent::__construct($settings, $level, $used);

        foreach ($arguments as $argument) {
            $this[$argument->name] = new InputValueDefinitionBlock(
                $this->getSettings(),
                $this->getLevel() + 1,
                $this->getUsed(),
                $argument,
            );
        }
    }

    protected function getPrefix(): string {
        return '(';
    }

    protected function getSuffix(): string {
        return ')';
    }

    protected function isWrapped(): bool {
        return true;
    }

    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeArguments();
    }
}
