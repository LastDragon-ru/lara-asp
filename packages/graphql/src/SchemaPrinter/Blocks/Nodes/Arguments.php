<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Nodes;

use GraphQL\Language\AST\ArgumentNode;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\BlockList;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;
use Traversable;

/**
 * @internal
 * @extends BlockList<Value>
 */
class Arguments extends BlockList {
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
            $this[$argument->name->value] = new Value(
                $this->getDispatcher(),
                $this->getSettings(),
                $this->getLevel() + 1,
                $this->getUsed(),
                $argument->value,
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
