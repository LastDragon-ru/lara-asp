<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Ast;

use GraphQL\Language\AST\ArgumentNode;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\BlockList;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Property;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;
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
        Dispatcher $dispatcher,
        Settings $settings,
        int $level,
        int $used,
        Traversable|array $arguments,
    ) {
        parent::__construct($dispatcher, $settings, $level, $used);

        foreach ($arguments as $argument) {
            $name        = $argument->name->value;
            $this[$name] = new Property(
                $this->getDispatcher(),
                $this->getSettings(),
                $name,
                new ValueNodeBlock(
                    $this->getDispatcher(),
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
}
