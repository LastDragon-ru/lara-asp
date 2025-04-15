<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\AstRestorer\Footnote;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\Extension\Footnote\Event\FixOrphanedFootnotesAndRefsListener;
use League\CommonMark\Extension\Footnote\Event\GatherFootnotesListener;
use League\CommonMark\Extension\Footnote\FootnoteExtension;
use Override;

/**
 * todo(documentator): [league/commonmark] [update] Check priorities in {@see FootnoteExtension}.
 *
 * @see FootnoteExtension
 * @see FixOrphanedFootnotesAndRefsListener
 * @see GatherFootnotesListener
 */

/**
 * Restore unused footnotes in the AST (they are deleting after parsing).
 *
 * @internal
 */
class Extension implements ExtensionInterface {
    public function __construct() {
        // empty
    }

    #[Override]
    public function register(EnvironmentBuilderInterface $environment): void {
        // Footnotes supported?
        $configuration = $environment->getConfiguration()->get('footnote');

        if ($configuration === null) {
            return;
        }

        // Add Listener
        $listener = $this->getListener();

        $environment
            /** Should be before {@see FixOrphanedFootnotesAndRefsListener} */
            ->addEventListener(DocumentParsedEvent::class, $listener->backup(...), 31)
            /** Should be after {@see GatherFootnotesListener} */
            ->addEventListener(DocumentParsedEvent::class, $listener->restore(...), 5);
    }

    protected function getListener(): Listener {
        return new Listener();
    }
}
