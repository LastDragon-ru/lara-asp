<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Ast;

use GraphQL\Language\AST\ArgumentNode;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\BlockList;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Property;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Misc\PrinterSettings;
use Traversable;

/**
 * @internal
 * @extends BlockList<Property<ValueNodeBlock>>
 */
class ArgumentNodeList extends BlockList {
    /**
     * @param Traversable<ArgumentNode>|array<ArgumentNode> $arguments
     */
    public function __construct(
        PrinterSettings $settings,
        int $level,
        int $used,
        Traversable|array $arguments,
    ) {
        parent::__construct($settings, $level, $used);

        foreach ($arguments as $argument) {
            $name        = $argument->name->value;
            $this[$name] = new Property(
                $this->getSettings(),
                $name,
                new ValueNodeBlock(
                    $this->getSettings(),
                    $this->getLevel() + 1,
                    $this->getUsed(),
                    $argument->value,
                ),
            );
        }
    }

    protected function getPrefix(): string {
        return '(';
    }

    protected function getSuffix(): string {
        return ')';
    }

    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeArguments();
    }

    protected function isAlwaysMultiline(): bool {
        return parent::isAlwaysMultiline()
            || $this->getSettings()->isAlwaysMultilineArguments();
    }
}
