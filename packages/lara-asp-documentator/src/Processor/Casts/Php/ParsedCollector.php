<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts\Php;

use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
class ParsedCollector extends NodeVisitorAbstract {
    /**
     * @var list<ClassLike>
     */
    public array $classes = [];

    public function __construct() {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function beforeTraverse(array $nodes): null {
        parent::beforeTraverse($nodes);

        $this->classes = [];

        return null;
    }

    #[Override]
    public function enterNode(Node $node): null {
        parent::enterNode($node);

        if ($node instanceof ClassLike) {
            $this->classes[] = $node;
        }

        return null;
    }
}
