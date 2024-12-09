<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\BlockPadding;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Lines;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Padding;
use League\CommonMark\Extension\Table\TableCell;
use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Node\Block\Document as DocumentNode;
use League\CommonMark\Node\Node;
use League\CommonMark\Util\UrlEncoder;

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
            ? BlockPadding::class
            : Padding::class;
        $padding = $type::optional()->get($container);

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
        $type::set($container, $padding);

        // Return
        return $padding;
    }

    public static function getLine(DocumentNode $document, int $line): ?string {
        $lines = Lines::get($document);
        $line  = $lines[$line] ?? null;

        return $line;
    }

    public static function getLinkTarget(Node $container, string $target, ?bool $wrap = null): string {
        $target = ($wrap ?? preg_match('/\s/u', $target) > 0)
            ? '<'.strtr($target, ['<' => '\\<', '>' => '\\>']).'>'
            : UrlEncoder::unescapeAndEncode($target);
        $target = self::escapeTextInTableCell($container, $target);

        return $target;
    }

    public static function getLinkTitle(Node $container, string $title, ?string $wrapper = null): string {
        if ($title === '') {
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
    public static function getParent(Node $node, string $class): ?object {
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
        if (str_starts_with($path, './') || str_starts_with($path, '../') || str_starts_with($path, '#')) {
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
            && (new FilePath($path))->isRelative();
    }

    public static function isPathToSelf(Document $document, FilePath|string $path): bool {
        $self = $document->path;
        $path = (string) parse_url((string) $path, PHP_URL_PATH);
        $is   = $path === '' || $path === '.' || $self === null || $self->isEqual($self->getFilePath($path));

        return $is;
    }
}
