<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Mutations;

use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference\Node as ReferenceNode;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Delete;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutation;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Utils;
use League\CommonMark\Node\Node;
use Override;

/**
 * Removes all instructions.
 *
 * @internal
 *
 * @implements Mutation<ReferenceNode>
 */
readonly class InstructionsRemove implements Mutation {
    public function __construct(
        private Instructions $instructions,
    ) {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function nodes(): array {
        return [
            ReferenceNode::class,
        ];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function mutagens(Document $document, Node $node): array {
        return Utils::isInstruction($node, $this->instructions)
            ? [new Delete(Location::get($node))]
            : [];
    }
}
