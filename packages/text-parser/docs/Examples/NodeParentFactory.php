<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Docs\Examples;

use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\TextParser\Ast\NodeChild;
use LastDragon_ru\TextParser\Ast\NodeParentFactory;
use LastDragon_ru\TextParser\Ast\NodeParentImpl;
use LastDragon_ru\TextParser\Ast\NodeString;
use Override;

// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses

/**
 * @implements NodeChild<ParentNode>
 */
class ChildNode extends NodeString implements NodeChild {
    // empty
}

/**
 * @extends NodeParentImpl<ChildNode>
 */
class ParentNode extends NodeParentImpl {
    // empty
}

/**
 * @extends NodeParentFactory<ParentNode, ChildNode>
 */
class ParentNodeFactory extends NodeParentFactory {
    /**
     * @inheritDoc
     */
    #[Override]
    protected function onCreate(array $children): ?object {
        return $children !== [] ? new ParentNode($children) : null;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    protected function onPush(array $children, ?object $node): bool {
        return true;
    }
}

$factory = new ParentNodeFactory();

$factory->push(new ChildNode('a'));
$factory->push(new ChildNode('b'));
$factory->push(new ChildNode('c'));

Example::dump($factory->create()); // create and reset
Example::dump($factory->create()); // `null`
