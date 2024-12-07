<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Mutations;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Processor\InstanceList;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Instruction;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Utils;
use League\CommonMark\Node\NodeIterator;
use Override;

/**
 * Removes all instructions.
 *
 * @internal
 */
readonly class InstructionsRemove implements Mutation {
    public function __construct(
        /**
         * @var InstanceList<Instruction<Parameters>>
         */
        private InstanceList $instructions,
    ) {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function __invoke(Document $document): iterable {
        // Just in case
        yield from [];

        // Update
        foreach ($document->getNode()->iterator(NodeIterator::FLAG_BLOCKS_ONLY) as $node) {
            // Instruction?
            if (!Utils::isInstruction($node, $this->instructions)) {
                continue;
            }

            // Change
            yield [Location::get($node), null];
        }
    }
}
