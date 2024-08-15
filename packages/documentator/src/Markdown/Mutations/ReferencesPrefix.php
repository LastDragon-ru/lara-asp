<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Location\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Reference\Block as Reference;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use League\CommonMark\Extension\CommonMark\Node\Inline\AbstractWebResource;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\Table\TableCell;
use League\CommonMark\Node\Block\Document as DocumentNode;
use League\CommonMark\Node\Inline\Text;
use Override;

use function hash;
use function mb_strlen;
use function str_replace;
use function uniqid;

/**
 * Adds unique prefix for all references.
 */
class ReferencesPrefix implements Mutation {
    public function __construct(
        /**
         * If the prefix is not specified, the hash of the document path will
         * be used. If the document path is unknown, the random hash will be
         * used.
         */
        protected ?string $prefix = null,
    ) {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function __invoke(Document $document, DocumentNode $node): array {
        $prefix     = $this->prefix ?: hash('xxh3', $document->getPath() ?: uniqid($this::class)); // @phpstan-ignore disallowed.function
        $changes    = [];
        $references = $this->getReferences($node);

        foreach ($references as $reference) {
            // Location?
            $location = Utils::getLocation($reference);

            if (!$location) {
                continue;
            }

            // Changes
            $text = null;

            if ($reference instanceof Link || $reference instanceof Image) {
                $label  = (string) Utils::getChild($reference, Text::class)?->getLiteral();
                $target = Utils::getReference($reference)?->getLabel();
                $target = "{$prefix}-{$target}";

                if (Utils::getContainer($reference) instanceof TableCell) {
                    $label  = str_replace('|', '\\|', $label);
                    $target = str_replace('|', '\\|', $target);
                }

                $text = Utils::getLink('[%s][%s]', $label, $target, '', null, null);

                if ($reference instanceof Image) {
                    $text = "!{$text}";
                }
            } elseif ($reference instanceof Reference) {
                $coordinate = null;

                foreach ($location as $c) {
                    $coordinate = $c;
                    break;
                }

                if ($coordinate) {
                    $startLine = $coordinate->line;
                    $endLine   = $startLine;
                    $offset    = $coordinate->offset + 1;
                    $length    = mb_strlen($reference->getLabel());
                    $text      = "{$prefix}-{$reference->getLabel()}";
                    $location  = new Location($startLine, $endLine, $offset, $length);
                }
            } else {
                // skipped
            }

            if ($text !== null) {
                $changes[] = [$location, $text];
            }
        }

        // Return
        return $changes;
    }

    /**
     * @return list<AbstractWebResource|Reference>
     */
    protected function getReferences(DocumentNode $node): array {
        $references = [];

        foreach ($node->iterator() as $child) {
            if ($child instanceof AbstractWebResource && Utils::isReference($child)) {
                $references[] = $child;
            } elseif ($child instanceof Reference) {
                $references[] = $child;
            } else {
                // empty
            }
        }

        return $references;
    }
}
