<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Utils;

use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Parser\MarkdownParser;

use function array_slice;
use function implode;
use function ltrim;
use function preg_split;
use function trim;

class Markdown {
    /**
     * Returns the first `# Header` if present.
     */
    public static function getTitle(string $string): ?string {
        $title = static::getText($string, static::getTitleNode($string));
        $title = $title ? ltrim($title, '# ') : null;
        $title = $title ?: null;

        return $title;
    }

    /**
     * Returns the first paragraph right after `# Header` if present.
     */
    public static function getSummary(string $string): ?string {
        $node    = static::getTitleNode($string)?->next();
        $summary = $node instanceof Paragraph
            ? static::getText($string, $node)
            : null;

        return $summary;
    }

    protected static function getDocumentNode(string $string): Document {
        $converter   = new GithubFlavoredMarkdownConverter();
        $environment = $converter->getEnvironment();
        $parser      = new MarkdownParser($environment);

        return $parser->parse($string);
    }

    protected static function getTitleNode(string $string): ?Heading {
        $node   = static::getDocumentNode($string)->firstChild();
        $header = $node instanceof Heading && $node->getLevel() === 1
            ? $node
            : null;

        return $header;
    }

    protected static function getText(string $string, ?AbstractBlock $node): ?string {
        // todo(documentator): There is no way to convert AST back to Markdown yet
        //      https://github.com/thephpleague/commonmark/issues/419
        if (!$node || $node->getStartLine() === null || $node->getEndLine() === null) {
            return null;
        }

        $start = $node->getStartLine() - 1;
        $end   = $node->getEndLine() - 1;
        $lines = (array) preg_split('/\R/u', $string);
        $lines = array_slice($lines, $start, $end - $start + 1);
        $text  = trim(implode("\n", $lines));

        return $text;
    }
}
