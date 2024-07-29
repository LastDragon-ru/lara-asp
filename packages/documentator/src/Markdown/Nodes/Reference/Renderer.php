<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Reference;

use LastDragon_ru\LaraASP\Documentator\Package;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Xml\XmlNodeRendererInterface;
use Override;

use function assert;
use function implode;
use function preg_replace;

/**
 * @internal
 */
class Renderer implements NodeRendererInterface, XmlNodeRendererInterface {
    #[Override]
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string {
        assert($node instanceof Block);

        return '';
    }

    #[Override]
    public function getXmlTagName(Node $node): string {
        assert($node instanceof Block);

        return Package::Name.'-reference';
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getXmlAttributes(Node $node): array {
        assert($node instanceof Block);

        return [
            'label'       => $this->escape($node->getLabel()),
            'destination' => $this->escape($node->getDestination()),
            'title'       => $this->escape($node->getTitle()),
            'startLine'   => $node->getStartLine() ?? 'null',
            'endLine'     => $node->getEndLine() ?? 'null',
            'offset'      => $node->getOffset(),
            'location'    => $this->location($node),
        ];
    }

    private function escape(string $string): string {
        return preg_replace('/\R/u', '\\n', $string) ?? $string;
    }

    private function location(Block $node): string {
        $location = [];

        foreach ($node->getLocation() as $line) {
            $location[] = '{'.implode(',', [$line->number, $line->offset, $line->length ?? 'null']).'}';
        }

        return '['.implode(',', $location).']';
    }
}
