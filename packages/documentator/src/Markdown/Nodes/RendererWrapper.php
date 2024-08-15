<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Nodes;

use LastDragon_ru\LaraASP\Documentator\Markdown\Data\BlockPadding;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Data;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Length;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Offset;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Padding;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use League\CommonMark\Environment\EnvironmentAwareInterface;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Xml\XmlNodeRendererInterface;
use League\Config\ConfigurationAwareInterface;
use Override;
use Stringable;

use function array_filter;
use function array_merge;
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
        $additional = $this->getXmlAdditionalAttributes($node);
        $attributes = $this->renderer instanceof XmlNodeRendererInterface
            ? $this->renderer->getXmlAttributes($node)
            : [];
        $attributes = array_merge($attributes, $additional);

        array_walk_recursive($attributes, function (mixed &$value): void {
            if (is_string($value)) {
                $value = $this->escape($value);
            }
        });

        return $attributes;
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

    /**
     * @return array<string, scalar>
     */
    private function getXmlAdditionalAttributes(Node $node): array {
        $attributes = [
            'location' => $this->location($node),
        ];
        $data       = [
            'offset'       => Offset::class,
            'length'       => Length::class,
            'padding'      => Padding::class,
            'blockPadding' => BlockPadding::class,
        ];

        foreach ($data as $key => $class) {
            $attributes[$key] = Data::get($node, $class);
        }

        return array_filter($attributes, static fn ($v) => $v !== null);
    }
}
