<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Reference\Block as Reference;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use League\CommonMark\Extension\CommonMark\Node\Inline\AbstractWebResource;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\Table\TableCell;
use League\CommonMark\Node\Block\Document as DocumentNode;
use League\CommonMark\Node\Inline\Text;
use Override;

use function rawurldecode;
use function str_replace;

/**
 * Inlines all references.
 */
class ReferencesInline implements Mutation {
    public function __construct() {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function __invoke(Document $document, DocumentNode $node): array {
        $changes    = [];
        $references = $this->getReferences($node);

        foreach ($references as $reference) {
            // Location?
            $location = Utils::getLocation($reference);

            if (!$location) {
                continue;
            }

            // Change
            $text = null;

            if ($reference instanceof Link || $reference instanceof Image) {
                $title  = (string) $reference->getTitle();
                $label  = (string) Utils::getChild($reference, Text::class)?->getLiteral();
                $target = rawurldecode($reference->getUrl());

                if (Utils::getContainer($reference) instanceof TableCell) {
                    $title  = str_replace('|', '\\|', $title);
                    $label  = str_replace('|', '\\|', $label);
                    $target = str_replace('|', '\\|', $target);
                }

                $text = $title
                    ? Utils::getLink('[%s](%s %s)', $label, $target, $title, null, null)
                    : Utils::getLink('[%s](%s)', $label, $target, '', null, null);

                if ($reference instanceof Image) {
                    $text = "!{$text}";
                }
            } elseif ($reference instanceof Reference) {
                $text = '';
            } else {
                // skipped
            }

            if ($text !== null) {
                $changes[] = [$location, $text ?: null];
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
