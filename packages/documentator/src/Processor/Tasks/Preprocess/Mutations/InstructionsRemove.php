<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Mutations;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Processor\InstanceList;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Instruction;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Utils;
use League\CommonMark\Node\Block\Document as DocumentNode;
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
    public function __invoke(Document $document, DocumentNode $node): iterable {
        $changes = [];

        foreach ($node->iterator(NodeIterator::FLAG_BLOCKS_ONLY) as $block) {
            // Instruction?
            if (!Utils::isInstruction($block, $this->instructions)) {
                continue;
            }

            // Location?
            $location = Location::get($block);

            if ($location !== null) {
                $changes[] = [$location, null];
            }
        }

        return $changes;
    }
}
