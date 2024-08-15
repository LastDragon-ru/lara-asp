<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use League\CommonMark\Node\Block\Document as DocumentNode;
use Override;

use function array_merge;

/**
 * Merges all mutations into one.
 */
readonly class Composite implements Mutation {
    /**
     * @var array<array-key, Mutation>
     */
    protected array $mutations;

    public function __construct(Mutation ...$mutations) {
        $this->mutations = $mutations;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function __invoke(Document $document, DocumentNode $node): array {
        $changes = [];

        foreach ($this->mutations as $mutation) {
            $changes = array_merge($changes, $mutation($document, $node));
        }

        return $changes;
    }
}
