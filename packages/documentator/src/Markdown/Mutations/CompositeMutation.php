<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use Override;

/**
 * Merges all mutations into one.
 */
readonly class CompositeMutation implements Mutation {
    /**
     * @var array<array-key, Mutation>
     */
    private array $mutations;

    public function __construct(Mutation ...$mutations) {
        $this->mutations = $mutations;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function __invoke(Document $document): iterable {
        // Just in case
        yield from [];

        // Process all
        foreach ($this->mutations as $mutation) {
            yield from $mutation($document);
        }
    }
}
