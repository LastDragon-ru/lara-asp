<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks;

use LastDragon_ru\LaraASP\Documentator\Composer\Package;
use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Append;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Generated\Node as GeneratedNode;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Changeset;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Delete;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Replace;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Resolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\FileTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Contracts\LinkFactory;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Exceptions\CodeLinkUnresolved;
use LastDragon_ru\LaraASP\Documentator\Utils\Text;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code as CodeNode;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link as LinkNode;
use Override;
use WeakMap;

use function array_map;
use function array_pop;
use function array_values;
use function implode;
use function is_array;
use function ksort;
use function mb_trim;
use function sort;
use function str_starts_with;

/**
 * Searches class/method/property/etc names in `inline code` and wrap it into a
 * link to file.
 *
 * It expects that the input directory is a composer project and will use
 * `psr-4` autoload rules to find class files. Classes which are not from the
 * composer will be completely ignored. If the file/class/method/etc doesn't
 * exist, the error will be thrown. To avoid the error, you can place `💀` mark
 * as the first character in `inline code`. Deprecated objects will be marked
 * automatically.
 *
 * Supported links:
 *
 * * `\App\Class`
 * * `\App\Class::method()`
 * * `\App\Class::$property`
 * * `\App\Class::Constant`
 */
class Task implements FileTask {
    protected const string BlockMarker       = 'code-links';
    protected const string DeprecationMarker = '💀';

    public function __construct(
        protected readonly LinkFactory $factory,
    ) {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function glob(): array|string {
        return '*.md';
    }

    #[Override]
    public function __invoke(Resolver $resolver, File $file): void {
        // Composer?
        $composer = $resolver->find('~input/composer.json');
        $package  = $composer?->as(Package::class);

        if (!($package instanceof Package)) {
            return;
        }

        // Parse
        $unresolved = [];
        $resolved   = [];
        $document   = $file->as(Document::class);
        $parsed     = $this->parse($document);

        // Links
        foreach ($parsed['links'] as $token) {
            // External?
            $paths = $token->link->getSource($file, $package);

            if ($paths === null) {
                continue;
            }

            // File?
            $source = null;
            $paths  = is_array($paths) ? $paths : [$paths];

            foreach ($paths as $path) {
                $source = $resolver->find($composer->path->resolve($path));

                if ($source !== null) {
                    break;
                }
            }

            if ($source === null && !$token->deprecated) {
                $unresolved[] = $token;
                continue;
            }

            // Target?
            $target = null;

            if ($source !== null) {
                $target = $token->link->getTarget($file, $source);

                if ($target === null && !$token->deprecated) {
                    $unresolved[] = $token;
                    continue;
                }
            }

            // Save
            $resolved[] = [$token, $target];
        }

        // Unresolved?
        if ($unresolved !== []) {
            $unresolved = array_map(static fn ($token) => (string) $token->link, $unresolved);

            sort($unresolved);

            throw new CodeLinkUnresolved($unresolved);
        }

        // Mutate
        $changes = $this->getChanges($document, $parsed['blocks'], $resolved);

        if ($changes !== []) {
            $resolver->save($file, $document->mutate(new Changeset($changes)));
        }
    }

    /**
     * @param list<GeneratedNode>                 $blocks
     * @param list<array{LinkToken, ?LinkTarget}> $links
     *
     * @return list<Replace|Delete>
     */
    private function getChanges(Document $document, array $blocks, array $links): array {
        // Prepare
        $changes = [];

        // Remove blocks
        $refsParentNode     = $document->node;
        $refsParentLocation = null;

        foreach ($blocks as $block) {
            $refsParentLocation = Location::get($block);
            $refsParentNode     = $block;
            $changes[]          = new Delete($refsParentLocation);
        }

        // Update links
        $duplicates = $this->getDuplicates($links);
        $references = [];

        foreach ($links as [$token, $target]) {
            $link  = (string) $token->link;
            $hash  = static::BlockMarker.'/'.Text::hash($link);
            $title = $token->link->getTitle();
            $title = $target !== null && $title !== null && $title !== '' && !isset($duplicates[$token])
                ? $title
                : $link;
            $title = $target === null || $target->deprecated
                ? static::DeprecationMarker.$title
                : $title;

            if ($target !== null) {
                $referenceTitle              = Utils::getLinkTitle($refsParentNode, $link);
                $referenceTarget             = Utils::getLinkTarget($refsParentNode, (string) $target);
                $references[$referenceTitle] = "[{$hash}]: {$referenceTarget}\n    {$referenceTitle}";
            }

            foreach ($token->nodes as $node) {
                $location  = Location::get($node);
                $linkTitle = Utils::escapeTextInTableCell($node, $title);
                $changes[] = new Replace($location, $target !== null ? "[`{$linkTitle}`][{$hash}]" : "`{$linkTitle}`");
            }
        }

        // References
        if ($references !== []) {
            ksort($references);

            $location  = $refsParentLocation ?? new Append();
            $content   = GeneratedNode::get(static::BlockMarker, implode("\n\n", $references))."\n";
            $changes[] = new Replace($location, $content);
        }

        // Return
        return $changes;
    }

    /**
     * @param list<array{LinkToken, ?LinkTarget}> $links
     *
     * @return WeakMap<LinkToken, true>
     */
    private function getDuplicates(array $links): WeakMap {
        /** @var WeakMap<LinkToken, true> $duplicates */
        $duplicates = new WeakMap();

        while (($link = array_pop($links)) !== null) {
            // Target?
            [$currentToken, $currentTarget] = $link;

            if ($currentTarget === null) {
                continue;
            }

            // Search
            foreach ($links as $key => [$token, $target]) {
                // Target?
                if ($target === null) {
                    unset($links[$key]);
                    continue;
                }

                // Similar?
                if (!$currentToken->link->isSimilar($token->link)) {
                    continue;
                }

                // Yep
                $duplicates[$currentToken] = true;
                $duplicates[$token]        = true;
            }
        }

        // Return
        return $duplicates;
    }

    /**
     * @return array{
     *      blocks: list<GeneratedNode>,
     *      links: list<LinkToken>,
     *      }
     */
    protected function parse(Document $document): array {
        $mark   = static::DeprecationMarker;
        $links  = [];
        $blocks = [];

        foreach ($document->node->iterator() as $node) {
            if ($node instanceof GeneratedNode && $node->id === static::BlockMarker) {
                $blocks[] = $node;
            } elseif ($node instanceof CodeNode) {
                $parent = $node->parent();
                $target = $node;
                $link   = $this->factory->create(mb_trim($node->getLiteral(), "`{$mark}"));

                if ($parent instanceof LinkNode) {
                    $target = $parent;
                    $link   = $this->factory->create((string) $parent->getTitle()) ?? $link;
                }

                if ($link === null) {
                    continue;
                }

                $key        = (string) $link;
                $deprecated = str_starts_with(mb_trim($node->getLiteral(), '`'), $mark);

                if (isset($links[$key])) {
                    $links[$key]->deprecated = $links[$key]->deprecated || $deprecated;
                    $links[$key]->nodes[]    = $target;
                } else {
                    $links[$key] = new LinkToken($link, $deprecated, [$target]);
                }
            } else {
                // skip
            }
        }

        return [
            'blocks' => $blocks,
            'links'  => array_values($links),
        ];
    }
}
