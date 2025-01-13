<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Utils;

use LastDragon_ru\LaraASP\Documentator\Markdown\Document as MarkdownDocument;
use LastDragon_ru\LaraASP\Documentator\Package;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Block\HtmlBlock;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\Node;
use League\CommonMark\Parser\MarkdownParser;

use function array_slice;
use function implode;
use function mb_ltrim;
use function mb_strlen;
use function mb_substr;
use function mb_trim;
use function min;
use function preg_split;
use function str_ends_with;
use function str_repeat;
use function str_starts_with;
use function trigger_deprecation;

use const PHP_INT_MAX;

// phpcs:disable PSR1.Files.SideEffects

trigger_deprecation(Package::Name, '7.0.0', 'Please use %s/%s instead.', MarkdownDocument::class, Text::class);

/**
 * @deprecated 7.0.0 Please use {@see MarkdownDocument}/{@see Text} instead.
 */
class Markdown {
    /**
     * Returns the first `# Header` if present.
     */
    public static function getTitle(string $string): ?string {
        $title = static::getText($string, static::getTitleNode($string));
        $title = $title !== null ? mb_ltrim($title, '# ') : null;
        $title = $title !== '' ? $title : null;

        return $title;
    }

    /**
     * Returns the first paragraph right after `# Header` if present.
     */
    public static function getSummary(string $string): ?string {
        $title   = static::getTitleNode($string);
        $summary = static::getFirstNode($title?->next(), Paragraph::class);
        $summary = $summary !== null
            ? static::getText($string, $summary)
            : null;

        return $summary;
    }

    /**
     * @param int<0, max> $spaces
     */
    public static function setPadding(string $string, int $spaces): string {
        $prefix = str_repeat(' ', $spaces);
        $lines  = preg_split('/\R/u', $string);
        $lines  = $lines !== false ? $lines : [];
        $cut    = PHP_INT_MAX;

        foreach ($lines as $line) {
            $trimmed = mb_ltrim($line);
            $padding = mb_strlen($line) - mb_strlen($trimmed);
            $cut     = min($cut, $padding);
        }

        foreach ($lines as $i => $line) {
            $line      = mb_substr($line, $cut);
            $line      = ($line !== '' ? $prefix : '').$line;
            $lines[$i] = $line;
        }

        return implode("\n", $lines);
    }

    protected static function getDocumentNode(string $string): Document {
        $converter   = new GithubFlavoredMarkdownConverter();
        $environment = $converter->getEnvironment();
        $parser      = new MarkdownParser($environment);

        return $parser->parse($string);
    }

    protected static function getTitleNode(string $string): ?Heading {
        $document = static::getDocumentNode($string);
        $header   = static::getFirstNode($document, Heading::class, static fn ($n) => $n->getLevel() === 1);

        return $header;
    }

    protected static function getText(string $string, ?AbstractBlock $node): ?string {
        // todo(documentator): There is no way to convert AST back to Markdown yet
        //      https://github.com/thephpleague/commonmark/issues/419
        $start = $node?->getStartLine();
        $end   = $node?->getEndLine();

        if ($start === null || $end === null) {
            return null;
        }

        $lines = preg_split('/\R/u', $string);
        $lines = $lines !== false ? $lines : [];
        $lines = array_slice($lines, $start - 1, $end - $start + 1);
        $text  = mb_trim(implode("\n", $lines));

        return $text;
    }

    /**
     * @template T of Node
     *
     * @param class-string<T>   $class
     * @param callable(T): bool $filter
     *
     * @return ?T
     */
    protected static function getFirstNode(?Node $node, string $class, ?callable $filter = null): ?Node {
        // Null?
        if ($node === null) {
            return null;
        }

        // Wanted?
        if ($node instanceof $class && ($filter === null || $filter($node))) {
            return $node;
        }

        // Comment?
        if (
            $node instanceof HtmlBlock
            && str_starts_with($node->getLiteral(), '<!--')
            && str_ends_with($node->getLiteral(), '-->')
        ) {
            return static::getFirstNode($node->next(), $class, $filter);
        }

        // Document?
        if ($node instanceof Document) {
            return static::getFirstNode($node->firstChild(), $class, $filter);
        }

        // Not found
        return null;
    }
}
