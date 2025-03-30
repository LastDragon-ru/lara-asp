<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Generated;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Generated\Data\EndMarkerLocation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Generated\Data\StartMarkerLocation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Generated\Node as GeneratedNode;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Delete;
use League\CommonMark\Node\Node;
use Override;

/**
 * Removes start and end marks of Generated block.
 *
 * @implements Mutation<GeneratedNode>
 */
readonly class Unwrap implements Mutation {
    public function __construct() {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function nodes(): array {
        return [
            GeneratedNode::class,
        ];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function mutagens(Document $document, Node $node): array {
        $startMarker = StartMarkerLocation::optional()->get($node);
        $endMarker   = EndMarkerLocation::optional()->get($node);
        $mutagens    = [];

        if ($startMarker !== null) {
            $mutagens[] = new Delete($startMarker);
        }

        if ($endMarker !== null) {
            $mutagens[] = new Delete($endMarker);
        }

        return $mutagens;
    }
}
