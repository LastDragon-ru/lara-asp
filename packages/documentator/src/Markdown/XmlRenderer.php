<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown;

use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Xml\XmlNodeRendererInterface;
use Override;

use function implode;
use function preg_replace;

/**
 * @internal
 */
abstract class XmlRenderer implements NodeRendererInterface, XmlNodeRendererInterface {
    #[Override]
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string {
        return '';
    }

    protected function escape(?string $string): string {
        return preg_replace('/\R/u', '\\n', $string ?? '') ?? $string ?? '';
    }

    protected function location(?Location $location): string {
        $lines = [];

        foreach ($location ?? [] as $line) {
            $lines[] = '{'.implode(',', [$line->line, $line->offset, $line->length ?? 'null']).'}';
        }

        return '['.implode(',', $lines).']';
    }
}
