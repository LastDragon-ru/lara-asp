<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutator;

use LastDragon_ru\LaraASP\Documentator\Editor\Editor;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\List\Mutagens;
use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Node\Node;

use function array_merge;
use function implode;
use function mb_trim;

/**
 * @internal
 */
class Mutator {
    /**
     * @var array<class-string<Node>, list<Mutation<covariant Node>>>
     */
    private array $mutations = [];

    /**
     * @var array<class-string<Node>, list<Mutation<covariant Node>>>
     */
    private array $nodes = [];

    /**
     * @param array<array-key, Mutation<covariant Node>|iterable<mixed, Mutation<covariant Node>>> $mutations
     */
    public function __construct(array $mutations) {
        foreach ($mutations as $mutation) {
            $this->add($mutation);
        }
    }

    /**
     * @param Mutation<covariant Node>|iterable<mixed, Mutation<covariant Node>> $mutations
     */
    private function add(Mutation|iterable $mutations): void {
        if ($mutations instanceof Mutation) {
            foreach ($mutations::nodes() as $node) {
                $this->mutations[$node][] = $mutations;
            }
        } else {
            foreach ($mutations as $mutation) {
                $this->add($mutation);
            }
        }
    }

    /**
     * @param array<int, string> $lines
     */
    public function mutate(Markdown $markdown, Document $document, array $lines): Document {
        // Empty?
        $mutagens = $this->getNodeMutagens(new Mutagens(), $document, $document->node);

        if ($mutagens->isEmpty()) {
            return $document;
        }

        // Apply
        $editor  = new Editor($lines);
        $mutated = [];

        foreach ($mutagens->getChanges() as $location => $changes) {
            $text = $editor;

            if ($location !== null) {
                $text = $text->extract([$location]);
            }

            if ($changes !== []) {
                $text = $text->mutate($changes);
            }

            $text = mb_trim((string) $text, "\n\r");

            if ($text !== '') {
                $mutated[] = $text;
            }
        }

        // Parse
        $mutated = implode("\n\n", $mutated).($mutated !== [] ? "\n" : '');
        $mutated = $markdown->parse($mutated, $document->path);

        // Finalize
        foreach ($mutagens->getFinalizers() as $finalizer) {
            ($finalizer)($mutated);
        }

        // Return
        return $mutated;
    }

    protected function getNodeMutagens(Mutagens $mutagens, Document $document, Node $node): Mutagens {
        // Skipped
        if ($node instanceof AbstractBlock) {
            $location = Location::optional()->get($node);

            if ($location !== null && $mutagens->isIgnored($location)) {
                return $mutagens;
            }
        }

        // Collect
        $mutations = $this->getNodeMutations($node);

        foreach ($mutations as $mutation) {
            $mutagens->addAll($mutation->mutagens($document, $node));
        }

        // Children
        $child = $node->firstChild();

        while ($child !== null) {
            $this->getNodeMutagens($mutagens, $document, $child);

            $child = $child->next();
        }

        // Return
        return $mutagens;
    }

    /**
     * @template T of Node
     *
     * @param T $node
     *
     * @return list<Mutation<T>>
     */
    protected function getNodeMutations(Node $node): array {
        if (!isset($this->nodes[$node::class])) {
            $this->nodes[$node::class] = [];

            foreach ($this->mutations as $class => $mutations) {
                if ($node instanceof $class) {
                    $this->nodes[$node::class] = array_merge($this->nodes[$node::class], $mutations);
                }
            }
        }

        return $this->nodes[$node::class]; // @phpstan-ignore return.type (https://github.com/phpstan/phpstan/issues/9521)
    }
}
