<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Locator;

use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Padding;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use League\CommonMark\Environment\EnvironmentAwareInterface;
use League\CommonMark\Environment\EnvironmentInterface;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\Footnote\Node\Footnote;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Node\NodeIterator;
use Override;

use function mb_ltrim;
use function mb_strlen;
use function mb_substr;

/**
 * Fix/Detect location/padding.
 *
 * Out the box only start/end line know. But not for all nodes, for example,
 * `Table`'s nodes don't have this information. Another important thing -
 * padding of the block node.
 *
 * @internal
 */
class Listener implements EnvironmentAwareInterface {
    private ?EnvironmentInterface $environment = null;

    public function __construct() {
        // empty
    }

    public function __invoke(DocumentParsedEvent $event): void {
        // Fix/Detect
        $document = $event->getDocument();

        foreach ($document->iterator(NodeIterator::FLAG_BLOCKS_ONLY) as $node) {
            if ($node instanceof Footnote) {
                $this->fixFootnote($document, $node);
            } else {
                // empty
            }
        }

        // Finalize Locations
        foreach ($this->environment?->getInlineParsers() ?? [] as $parser) {
            if ($parser instanceof Parser) {
                $parser->finalize();
            }
        }
    }

    #[Override]
    public function setEnvironment(EnvironmentInterface $environment): void {
        $this->environment = $environment;
    }

    private function fixFootnote(Document $document, Footnote $footnote): void {
        // Possible?
        $start = $footnote->getStartLine();
        $end   = $footnote->getEndLine();

        if ($start === null || $end === null) {
            return;
        }

        // Initial
        $initial = Utils::getPadding($footnote, $start, '[^');

        if ($initial === null) {
            return;
        }

        if ($start === $end) {
            return;
        }

        // Internal
        $padding = null;
        $index   = $start + 1;

        do {
            $line    = (string) Utils::getLine($document, $index++);
            $line    = mb_substr($line, $initial);
            $trimmed = mb_ltrim($line);
            $padding = mb_strlen($line) - mb_strlen($trimmed);
        } while ($index < $end && $trimmed === '');

        Padding::set($footnote, $padding);
    }
}
