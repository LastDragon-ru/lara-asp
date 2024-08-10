<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Nodes;

use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use League\CommonMark\Environment\EnvironmentAwareInterface;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Xml\XmlNodeRendererInterface;
use League\Config\ConfigurationAwareInterface;
use Override;
use Stringable;

use function array_walk_recursive;
use function implode;
use function is_string;
use function preg_replace;

/**
 * @internal
 */
readonly class RendererWrapper implements
    NodeRendererInterface,
    XmlNodeRendererInterface,
    EnvironmentAwareInterface,
    ConfigurationAwareInterface {
    use Aware;

    public function __construct(
        protected NodeRendererInterface|XmlNodeRendererInterface $renderer,
    ) {
        // empty
    }

    #[Override]
    protected function getObject(): object {
        return $this->renderer;
    }

    #[Override]
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): Stringable|string|null {
        return $this->renderer instanceof NodeRendererInterface
            ? $this->renderer->render($node, $childRenderer)
            : '';
    }

    #[Override]
    public function getXmlTagName(Node $node): string {
        return $this->renderer instanceof XmlNodeRendererInterface
            ? $this->renderer->getXmlTagName($node)
            : '';
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getXmlAttributes(Node $node): array {
        $attrs    = $this->renderer instanceof XmlNodeRendererInterface
            ? $this->renderer->getXmlAttributes($node)
            : [];
        $location = $this->location($node);

        if ($location !== null) {
            $attrs['location'] = $location;
        }

        array_walk_recursive($attrs, function (mixed &$value): void {
            if (is_string($value)) {
                $value = $this->escape($value);
            }
        });

        return $attrs;
    }

    protected function escape(string $string): string {
        return preg_replace('/\R/u', '\\n', $string) ?? $string;
    }

    protected function location(Node $node): ?string {
        $lines    = [];
        $location = Utils::getLocation($node) ?? [];

        foreach ($location as $line) {
            $lines[] = '{'.implode(',', [$line->line, $line->offset, $line->length ?? 'null']).'}';
        }

        return $lines ? '['.implode(',', $lines).']' : null;
    }
}
