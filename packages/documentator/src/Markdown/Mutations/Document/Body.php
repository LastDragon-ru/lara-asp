<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document;

use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Lines as LinesData;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Extract;
use League\CommonMark\Node\Block\Document as DocumentNode;
use League\CommonMark\Node\Node;
use Override;

use function array_key_first;
use function array_key_last;

/**
 * @implements Mutation<DocumentNode>
 */
readonly class Body implements Mutation {
    public function __construct() {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function nodes(): array {
        return [
            DocumentNode::class,
        ];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function mutagens(Document $document, Node $node): array {
        $lines         = LinesData::get($node);
        $startLine     = array_key_first($lines);
        $bodyEndLine   = array_key_last($lines);
        $bodyStartLine = (SummaryData::get($document->node) ?? TitleData::get($document->node))?->getEndLine();
        $mutagens      = $bodyStartLine !== null && $bodyStartLine + 1 > $startLine
            ? [new Extract(new Location($bodyStartLine + 1, $bodyEndLine ?? $bodyStartLine + 1))]
            : [];

        return $mutagens;
    }
}
