<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Reference;

use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Reference;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference\Node as ReferenceNode;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutation;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image as ImageNode;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link as LinkNode;
use Override;

/**
 * @implements Mutation<LinkNode|ImageNode|ReferenceNode>
 */
abstract readonly class Base implements Mutation {
    public function __construct() {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function nodes(): array {
        return [
            LinkNode::class,
            ImageNode::class,
            ReferenceNode::class,
        ];
    }

    protected function isReference(LinkNode|ImageNode|ReferenceNode $node): bool {
        return $node instanceof ReferenceNode
            || Reference::get($node) !== null;
    }
}
