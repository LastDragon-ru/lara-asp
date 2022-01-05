<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Printer\Blocks\Nodes;

use GraphQL\Language\AST\ArgumentNode;
use GraphQL\Language\AST\NodeList;
use LastDragon_ru\LaraASP\GraphQL\Printer\Blocks\ArgumentsBlockList;
use LastDragon_ru\LaraASP\GraphQL\Printer\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQL\Printer\Settings;

/**
 * @internal
 */
class Arguments extends Block {
    /**
     * @param NodeList<ArgumentNode> $arguments
     */
    public function __construct(
        Settings $settings,
        int $level,
        int $used,
        protected NodeList $arguments,
    ) {
        parent::__construct($settings, $level, $used);
    }

    protected function content(): string {
        $arguments = new ArgumentsBlockList($this->getSettings(), $this->getLevel(), $this->getUsed());

        foreach ($this->arguments as $argument) {
            $arguments[$argument->name->value] = new Value(
                $this->getSettings(),
                $this->getLevel() + 1,
                $this->getUsed(),
                $argument->value,
            );
        }

        return (string) $arguments;
    }
}
