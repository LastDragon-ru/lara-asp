<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Generated;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use Override;

class Renderer implements NodeRendererInterface {
    public function __construct() {
        // empty
    }

    #[Override]
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): mixed {
        return $childRenderer->renderNodes($node->children());
    }
}
