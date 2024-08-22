<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown;

use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\BlockPadding as DataBlockPadding;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Data;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Length as DataLength;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Lines as DataLines;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location as DataLocation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Offset as DataOffset;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Padding as DataPadding;
use LastDragon_ru\LaraASP\Documentator\Markdown\Location\Location;
use League\CommonMark\Extension\CommonMark\Node\Inline\AbstractWebResource;
use League\CommonMark\Extension\Table\TableCell;
use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Node\Block\Document as DocumentNode;
use League\CommonMark\Node\Node;
use League\CommonMark\Reference\ReferenceInterface;
use League\CommonMark\Util\UrlEncoder;

use function basename;
use function filter_var;
use function mb_strpos;
use function parse_url;
use function preg_match;
use function str_contains;
use function str_replace;
use function str_starts_with;
use function strtr;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_URL;
use const PHP_URL_PATH;

/**
 * @internal
 */
class Utils {
    public static function getDocument(Node $node): ?DocumentNode {
        return self::getParent($node, DocumentNode::class);
    }

    public static function getContainer(Node $node): ?AbstractBlock {
        return self::getParent($node, AbstractBlock::class);
    }

    /**
     * @template T of Node
     *
     * @param class-string<T> $class
     *
     * @return ?T
     */
    public static function getChild(Node $node, string $class): ?Node {
        $object = null;

        foreach ($node->children() as $child) {
            if ($child instanceof $class) {
                $object = $child;
                break;
            }
        }

        return $object;
    }

    public static function getPosition(Node $node): int {
        $position = 0;

        while ($node = $node->previous()) {
            $position++;
        }

        return $position;
    }

    /**
     * Detect block padding. We are expecting that all lines except first inside
     * the block have the same padding.
     */
    public static function getPadding(Node $node, ?int $line, ?string $start): ?int {
        // Container?
        $container = self::getContainer($node);

        if ($container === null) {
            return null;
        }

        // Known?
        $type    = $line === null || $line === $container->getStartLine()
            ? DataBlockPadding::class
            : DataPadding::class;
        $padding = Data::get($container, $type);

        if ($padding !== null) {
            return $padding;
        }

        // Possible?
        if ($line === null || $start === null) {
            return null;
        }

        // Document?
        $document = self::getDocument($container);

        if ($document === null) {
            return null;
        }

        // Detect
        $padding = mb_strpos(self::getLine($document, $line) ?? '', $start);

        if ($padding === false) {
            return null;
        }

        // Cache
        Data::set($container, new $type($padding));

        // Return
        return $padding;
    }

    public static function getLine(DocumentNode $document, int $line): ?string {
        $lines = Data::get($document, DataLines::class) ?? [];
        $line  = $lines[$line] ?? null;

        return $line;
    }

    public static function getLocation(Node $node): ?Location {
        $location = Data::get($node, DataLocation::class);

        if ($location === null && $node instanceof AbstractBlock) {
            $start   = $node->getStartLine();
            $end     = $node->getEndLine();
            $offset  = Data::get($node, DataOffset::class) ?? 0;
            $length  = Data::get($node, DataLength::class);
            $padding = self::getPadding($node, null, null);

            if ($padding === null && $node->parent() instanceof DocumentNode) {
                $padding = 0;
            }

            if ($start !== null && $end !== null && $padding !== null) {
                $location = new Location($start, $end, $offset, $length, $padding);
            }
        }

        return $location;
    }

    public static function getLengthLocation(Location $location, ?int $length): Location {
        return new Location(
            $location->startLine,
            $location->endLine,
            $location->offset,
            $length,
            $location->startLinePadding,
            $location->internalPadding,
        );
    }

    public static function getOffsetLocation(Location $location, int $offset): Location {
        return new Location(
            $location->startLine,
            $location->endLine,
            $location->offset + $offset,
            $location->length !== null ? $location->length - $offset : $location->length,
            $location->startLinePadding,
            $location->internalPadding,
        );
    }

    public static function isReference(AbstractWebResource $node): bool {
        return self::getReference($node) !== null;
    }

    public static function getReference(AbstractWebResource $node): ?ReferenceInterface {
        $reference = $node->data->get('reference', null);
        $reference = $reference instanceof ReferenceInterface ? $reference : null;

        return $reference;
    }

    public static function getLinkTarget(Node $container, string $target, ?bool $wrap = null): string {
        $target = ($wrap ?? preg_match('/\s/u', $target))
            ? '<'.strtr($target, ['<' => '\\<', '>' => '\\>']).'>'
            : UrlEncoder::unescapeAndEncode($target);
        $target = self::escapeTextInTableCell($container, $target);

        return $target;
    }

    public static function getLinkTitle(Node $container, string $title, ?string $wrapper = null): string {
        if (!$title) {
            return '';
        }

        $wrappers = [
            ')' => ['(' => '\\(', ')' => '\\)'],
            '"' => ['"' => '\\"'],
            "'" => ["'" => "\\'"],
        ];
        $wrapper  = match (true) {
            isset($wrappers[$wrapper]) => $wrapper,
            !str_contains($title, '"') => '"',
            !str_contains($title, "'") => "'",
            default                    => ')',
        };
        $title = match ($wrapper) {
            '"'     => '"'.strtr($title, $wrappers['"']).'"',
            "'"     => "'".strtr($title, $wrappers['"'])."'",
            default => '('.strtr($title, $wrappers[')']).')',
        };
        $title = self::escapeTextInTableCell($container, $title);

        return $title;
    }

    public static function escapeTextInTableCell(Node $container, string $text): string {
        if (self::getContainer($container) instanceof TableCell) {
            $text = str_replace('|', '\\|', $text);
        }

        return $text;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return ?T
     */
    private static function getParent(Node $node, string $class): ?object {
        $parent = null;

        do {
            if ($node instanceof $class) {
                $parent = $node;
                break;
            }

            $node = $node->parent();
        } while ($node);

        return $parent;
    }

    public static function isPathRelative(string $path): bool {
        // Fast
        if (str_starts_with($path, './') || str_starts_with($path, '../')) {
            return true;
        } elseif (str_starts_with($path, '/')) {
            return false;
        } else {
            // empty
        }

        // Long
        return filter_var($path, FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE) === null
            && !str_starts_with($path, 'tel:+') // see https://www.php.net/manual/en/filter.filters.validate.php
            && !str_starts_with($path, 'urn:')  // see https://www.php.net/manual/en/filter.filters.validate.php
            && Path::isRelative($path);
    }

    public static function isPathToSelf(string $path, ?Document $document = null): bool {
        $name = Path::normalize(basename($document?->getPath() ?? ''));
        $path = Path::normalize(parse_url($path, PHP_URL_PATH) ?: '');
        $self = $path === '' || ($name && $path === $name);

        return $self;
    }
}
