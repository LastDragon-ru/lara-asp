<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\AstRestorer\Footnote;

use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\AstRestorer\Node;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\Footnote\Node\Footnote;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Node\NodeIterator;
use WeakMap;

/**
 * @internal
 */
class Listener {
    /**
     * @var WeakMap<Document, list<Footnote>>
     */
    protected WeakMap $documents;

    public function __construct() {
        $this->documents = new WeakMap();
    }

    public function backup(DocumentParsedEvent $event): void {
        $document                   = $event->getDocument();
        $this->documents[$document] = [];

        foreach ($document->iterator(NodeIterator::FLAG_BLOCKS_ONLY) as $node) {
            if ($node instanceof Footnote) {
                $this->documents[$document][] = $node;
            }
        }
    }

    public function restore(DocumentParsedEvent $event): void {
        // Restore
        $document  = $event->getDocument();
        $footnotes = $this->documents[$document] ?? [];
        $container = new Node();

        foreach ($footnotes as $footnote) {
            if ($footnote->parent() === null) {
                $container->appendChild($footnote);
            }
        }

        // Add
        if ($container->hasChildren()) {
            $document->appendChild($container);
        }

        // Cleanup
        unset($this->documents[$document]);
    }
}
