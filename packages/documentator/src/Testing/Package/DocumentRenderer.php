<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Testing\Package;

use Closure;
use Exception;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown as MarkdownContract;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Data;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Environment\Markdown;
use LastDragon_ru\LaraASP\Documentator\Utils\Sorter;
use LastDragon_ru\LaraASP\Documentator\Utils\SortOrder;
use League\CommonMark\Environment\EnvironmentInterface;
use League\CommonMark\Node\Node;
use League\CommonMark\Node\StringContainerInterface;
use League\CommonMark\Reference\ReferenceInterface;
use League\CommonMark\Xml\XmlNodeRendererInterface;
use XMLWriter;

use function array_key_exists;
use function count;
use function get_object_vars;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_object;
use function is_string;
use function uksort;
use function var_export;

/**
 * @internal
 */
class DocumentRenderer {
    /**
     * @var array<class-string<Node>, ?XmlNodeRendererInterface>
     */
    private array $renderers = [];
    /**
     * @var Closure(string,string): int
     */
    private Closure               $sort;
    private ?EnvironmentInterface $env;

    private XMLWriter $xml;

    public function __construct(Sorter $sorter) {
        $this->sort = $sorter->forString(SortOrder::Asc);
        $this->xml  = new XMLWriter();
        $this->env  = null;
    }

    public function render(Document $document): string {
        try {
            $this->env = $this->getEnvironment($document);

            $this->xml->openMemory();
            $this->xml->setIndent(true);
            $this->xml->setIndentString('    ');
            $this->xml->startDocument(encoding: 'UTF-8');
            $this->xml->startElement('markdown');

            $this->writeNode($document->node);

            $this->xml->endElement();
            $this->xml->endDocument();

            return $this->xml->outputMemory();
        } finally {
            $this->reset();
        }
    }

    private function reset(): void {
        $this->renderers = [];
        $this->xml       = new XMLWriter();
        $this->env       = null;
    }

    private function writeNode(Node $node): void {
        $renderer = $this->getRenderer($node::class);

        $this->xml->startElement('node');

        if ($renderer !== null) {
            $this->xml->writeAttribute('name', $renderer->getXmlTagName($node));
            $this->writeAttributes($renderer->getXmlAttributes($node));
        } else {
            $this->xml->writeAttribute('class', $node::class);
        }

        $this->writeData($node->data->export());

        foreach ($node->children() as $child) {
            $this->writeNode($child);
        }

        if ($node instanceof StringContainerInterface) {
            $this->writeValue($node->getLiteral());
        }

        $this->xml->endElement();
    }

    /**
     * @param array<string, scalar> $attributes
     */
    private function writeAttributes(array $attributes): void {
        // Empty?
        if ($attributes === []) {
            return;
        }

        // Write
        $this->xml->startElement('attributes');

        foreach ($this->sort($attributes) as $attribute => $value) {
            $this->writeAttribute($attribute, $value);
        }

        $this->xml->endElement();
    }

    private function writeAttribute(string $attribute, mixed $value): void {
        $this->writeKeyValue('attribute', 'name', $attribute, $value);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function writeData(array $data): void {
        // Empty?
        if ($data === []) {
            return;
        }

        // Write
        $this->xml->startElement('data');

        foreach ($this->sort($data) as $key => $value) {
            $this->writeDataItem($key, $value);
        }

        $this->xml->endElement();
    }

    private function writeDataItem(string $key, mixed $value): void {
        $this->writeArrayItem($key, $value);
    }

    private function writeValue(mixed $value): void {
        if ($value === null) {
            $this->xml->writeElement('null');
        } elseif (is_bool($value)) {
            $this->xml->writeElement('bool', $value ? 'true' : 'false');
        } elseif (is_string($value)) {
            $this->xml->startElement('string');
            $this->xml->writeCdata($value);
            $this->xml->endElement();
        } elseif (is_float($value)) {
            $this->xml->writeElement('float', var_export($value, true));
        } elseif (is_int($value)) {
            $this->xml->writeElement('int', var_export($value, true));
        } elseif (is_array($value)) {
            $this->writeArray($value);
        } elseif (is_object($value)) {
            $this->writeObject($value);
        } else {
            throw new Exception('Not implemented.');
        }
    }

    private function writeObject(object $object): void {
        // Wrapper?
        if ($object instanceof Data) {
            $this->writeValue($object->value);

            return;
        }

        // Nope
        $this->xml->startElement('object');
        $this->xml->writeAttribute('class', $object::class);

        $properties = $this->getObjectProperties($object);
        $properties = $this->sort($properties);

        foreach ($properties as $property => $value) {
            $this->writeObjectProperty($property, $value);
        }

        $this->xml->endElement();
    }

    private function writeObjectProperty(string $property, mixed $value): void {
        $this->writeKeyValue('property', 'name', $property, $value);
    }

    /**
     * @param array<array-key, mixed> $array
     */
    private function writeArray(array $array): void {
        $this->xml->startElement('array');
        $this->xml->writeAttribute('length', (string) count($array));

        foreach ($array as $key => $value) {
            $this->writeArrayItem($key, $value);
        }

        $this->xml->endElement();
    }

    private function writeArrayItem(string|int $key, mixed $value): void {
        $this->writeKeyValue('item', 'key', $key, $value);
    }

    private function writeKeyValue(string $element, string $attribute, string|int $key, mixed $value): void {
        $this->xml->startElement($element);
        $this->xml->writeAttribute($attribute, (string) $key);

        $this->writeValue($value);

        $this->xml->endElement();
    }

    /**
     * @return array<string, mixed>
     */
    private function getObjectProperties(object $object): array {
        $properties = get_object_vars($object);
        $additional = match (true) {
            $object instanceof ReferenceInterface => [
                'label'       => $object->getLabel(),
                'title'       => $object->getTitle(),
                'destination' => $object->getDestination(),
            ],
            default                               => [],
        };

        return $properties + $additional;
    }

    /**
     * @template T of array-key
     *
     * @param array<T, mixed> $array
     *
     * @return array<T, mixed>
     */
    private function sort(array $array): array {
        uksort($array, $this->sort);

        return $array;
    }

    private function getEnvironment(Document $document): ?EnvironmentInterface {
        $documentWrapper = new class ($document) extends Document {
            /**
             * @noinspection             PhpMissingParentConstructorInspection
             * @phpstan-ignore-next-line constructor.missingParentCall (no need to call parent `__construct`)
             */
            public function __construct(
                private readonly Document $document,
            ) {
                // empty
            }

            public function getMarkdown(): MarkdownContract {
                return $this->document->markdown;
            }
        };
        $markdownWrapper = new class ($documentWrapper->getMarkdown()) extends Markdown {
            /**
             * @noinspection             PhpMissingParentConstructorInspection
             * @phpstan-ignore-next-line constructor.missingParentCall (no need to call parent `__construct`)
             */
            public function __construct(
                private readonly MarkdownContract $markdown,
            ) {
                // empty
            }

            public function getEnvironment(): ?EnvironmentInterface {
                return $this->markdown instanceof Markdown ? $this->markdown->environment : null;
            }
        };
        $environment     = $markdownWrapper->getEnvironment();

        return $environment;
    }

    /**
     * @param class-string<Node> $class
     */
    private function getRenderer(string $class): ?XmlNodeRendererInterface {
        if (!array_key_exists($class, $this->renderers)) {
            $this->renderers[$class] = null;

            foreach ($this->env?->getRenderersForClass($class) ?? [] as $renderer) {
                if ($renderer instanceof XmlNodeRendererInterface) {
                    $this->renderers[$class] = $renderer;
                    break;
                }
            }
        }

        return $this->renderers[$class];
    }
}
