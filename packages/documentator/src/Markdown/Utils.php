<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use League\CommonMark\Extension\Table\TableCell;
use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Node\Node;
use League\CommonMark\Util\UrlEncoder;

use function filter_var;
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
    public static function getContainer(Node $node): ?AbstractBlock {
        return self::getParent($node, AbstractBlock::class);
    }

    public static function getPosition(Node $node): int {
        $position = 0;

        while ($node = $node->previous()) {
            $position++;
        }

        return $position;
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
