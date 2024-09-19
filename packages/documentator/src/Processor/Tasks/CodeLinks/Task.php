<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks;

use Generator;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Documentator\Composer\Package;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Location\Append;
use LastDragon_ru\LaraASP\Documentator\Markdown\Location\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Changeset;
use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Generated\Block as GeneratedNode;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task as TaskContract;
use LastDragon_ru\LaraASP\Documentator\Processor\Dependencies\FileReference;
use LastDragon_ru\LaraASP\Documentator\Processor\Dependencies\Optional;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Composer;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Markdown;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\PhpClassComment;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Contracts\LinkFactory;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Exceptions\CodeLinkUnresolved;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links\ClassConstantLink;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links\ClassLink;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links\ClassMethodLink;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links\ClassPropertyLink;
use LastDragon_ru\LaraASP\Documentator\Utils\PhpDoc;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code as CodeNode;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link as LinkNode;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassLike;

use function array_map;
use function array_values;
use function hash;
use function implode;
use function ksort;
use function ltrim;
use function sort;
use function str_starts_with;
use function trim;

/**
 * Searches class/method/property/etc names in `inline code` and wrap it into a
 * link to file.
 *
 * It expects that the `$root` directory is a composer project and will use
 * `psr-4` autoload rules to find class files. Classes which are not from the
 * composer will be completely ignored. If the file/class/method/etc doesn't
 * exist, the error will be thrown. To avoid the error, you can place `💀` mark
 * as the first character in `inline code`. Deprecated objects will be marked
 * automatically.
 *
 * Supported links:
 * * `\App\Class`
 * * `\App\Class::method()`
 * * `\App\Class::$property`
 * * `\App\Class::Constant`
 */
class Task implements TaskContract {
    protected const BlockMarker       = 'code-links';
    protected const DeprecationMarker = '💀';

    public function __construct(
        protected readonly LinkFactory $factory,
        protected readonly Markdown $markdown,
        protected readonly Composer $composer,
        protected readonly PhpClassComment $comment,
    ) {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function getExtensions(): array {
        return ['md'];
    }

    /**
     * @return Generator<mixed, Dependency<*>, mixed, bool>
     */
    #[Override]
    public function __invoke(Directory $root, File $file): Generator {
        // Composer?
        $composer = $root->getPath('composer.json');
        $composer = Cast::toNullable(File::class, yield new Optional(new FileReference($composer)));
        $composer = $composer?->getMetadata($this->composer);

        if (!($composer instanceof Package)) {
            return true;
        }

        // Markdown?
        $document = $file->getMetadata($this->markdown);

        if (!$document || $document->isEmpty()) {
            return true;
        }

        // Parse
        $unresolved = [];
        $resolved   = [];
        $parsed     = $this->parse($document);

        // Links
        foreach ($parsed['links'] as $token) {
            // External?
            $paths = $token->link->getSource($root, $file, $composer);

            if ($paths === null) {
                continue;
            }

            // File?
            $source = null;

            foreach ((array) $paths as $path) {
                $source = Cast::toNullable(File::class, yield new Optional(new FileReference($path)));

                if ($source) {
                    break;
                }
            }

            if (!$source && !$token->deprecated) {
                $unresolved[] = $token;
                continue;
            }

            // Target?
            $target = null;

            if ($source) {
                $target = $this->getLinkTokenTarget($root, $file, $token, $source);

                if (!$target) {
                    $unresolved[] = $token;
                    continue;
                }
            }

            // Save
            $resolved[] = [$token, $target];
        }

        // Unresolved?
        if ($unresolved) {
            $unresolved = array_map(static fn ($token) => (string) $token->link, $unresolved);

            sort($unresolved);

            throw new CodeLinkUnresolved($unresolved);
        }

        // Mutate
        $changes = $this->getChanges($document, $parsed['blocks'], $resolved);

        if ($changes) {
            $file->setContent(
                (string) $document->mutate(new Changeset($changes)),
            );
        }

        // Done
        return true;
    }

    /**
     * @param list<GeneratedNode>                 $blocks
     * @param list<array{LinkToken, ?LinkTarget}> $links
     *
     * @return list<array{Location, ?string}>
     */
    private function getChanges(Document $document, array $blocks, array $links): array {
        // Prepare
        $changes = [];

        // Remove blocks
        $refsParentNode     = $document->getNode();
        $refsParentLocation = null;

        foreach ($blocks as $block) {
            $refsParentNode     = $block;
            $refsParentLocation = Utils::getLocation($block);

            if ($refsParentLocation) {
                $changes[] = [$refsParentLocation, null];
            }
        }

        // Group links
        $titles     = [];
        $duplicates = [];

        foreach ($links as [$token, $target]) {
            $title = $token->link->getTitle();

            if (!$target || !$title) {
                continue;
            }

            if (isset($titles[$title])) {
                $duplicates[$title] = true;
            } else {
                $titles[$title] = true;
            }
        }

        // Update links
        $references = [];

        foreach ($links as [$token, $target]) {
            $link  = (string) $token->link;
            $hash  = static::BlockMarker.'-'.hash('xxh3', $link);
            $title = $token->link->getTitle();
            $title = $target && $title && !isset($duplicates[$title])
                ? $title
                : $link;
            $title = !$target || $target->deprecated
                ? static::DeprecationMarker.$title
                : $title;

            if ($target) {
                $referenceTitle              = Utils::getLinkTitle($refsParentNode, $link);
                $referenceTarget             = Utils::getLinkTarget($refsParentNode, (string) $target);
                $references[$referenceTitle] = "[{$hash}]: {$referenceTarget} {$referenceTitle}";
            }

            foreach ($token->nodes as $node) {
                $location = Utils::getLocation($node);

                if ($location) {
                    $linkTitle = Utils::escapeTextInTableCell($node, $title);
                    $changes[] = [$location, $target ? "[`{$linkTitle}`][{$hash}]" : "`{$linkTitle}`"];
                }
            }
        }

        // References
        if ($references) {
            ksort($references);

            $location = $refsParentLocation ?? new Append();
            $content  = GeneratedNode::get(static::BlockMarker, implode("\n\n", $references))."\n";

            if ($location instanceof Append) {
                $content = "\n{$content}";
            }

            $changes[] = [$location, $content];
        }

        // Return
        return $changes;
    }

    private function normalize(string $class): string {
        return '\\'.ltrim($class, '\\');
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

        foreach ($document->getNode()->iterator() as $node) {
            if ($node instanceof GeneratedNode && $node->id === static::BlockMarker) {
                $blocks[] = $node;
            } elseif ($node instanceof CodeNode) {
                $parent = $node->parent();
                $target = $node;
                $link   = $this->factory->create(trim($node->getLiteral(), "`{$mark}"));

                if ($parent instanceof LinkNode) {
                    $target = $parent;
                    $link   = $this->factory->create((string) $parent->getTitle()) ?? $link;
                }

                if (!$link) {
                    continue;
                }

                $key        = (string) $link;
                $deprecated = str_starts_with(trim($node->getLiteral(), '`'), $mark);

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

    protected function getLinkTokenTarget(Directory $root, File $file, LinkToken $token, File $source): ?LinkTarget {
        // Class?
        $comment = $source->getMetadata($this->comment);

        if (!$comment) {
            return null;
        }

        // Resolve
        $target       = null;
        $path         = $source->getRelativePath($file);
        $deprecated   = $comment->comment->isDeprecated();
        $isClassMatch = function (ClassLike $classLike, string $class): bool {
            return $this->normalize((string) $classLike->namespacedName) === $class;
        };

        if ($token->link instanceof ClassLink) {
            if ($isClassMatch($comment->class, $token->link->class)) {
                $target = new LinkTarget($path, $deprecated, null, null);
            }
        } elseif ($token->link instanceof ClassConstantLink) {
            if ($isClassMatch($comment->class, $token->link->class)) {
                // No method :'(
                foreach ($comment->class->getConstants() as $constant) {
                    foreach ($constant->consts as $const) {
                        if ((string) $const->name === $token->link->constant) {
                            $target = $this->target($path, $constant, $deprecated);
                            break;
                        }
                    }
                }
            }
        } elseif ($token->link instanceof ClassMethodLink) {
            if ($isClassMatch($comment->class, $token->link->class)) {
                $node   = $comment->class->getMethod($token->link->method);
                $target = $this->target($path, $node, $deprecated);
            }
        } elseif ($token->link instanceof ClassPropertyLink) {
            if ($isClassMatch($comment->class, $token->link->class)) {
                $node = $comment->class->getProperty($token->link->property);

                if ($node === null) {
                    $constructor = $comment->class->getMethod('__construct');
                    $parameters  = $constructor?->getParams() ?? [];

                    foreach ($parameters as $parameter) {
                        if (!$parameter->isPromoted()) {
                            continue;
                        }

                        if ($parameter->var instanceof Variable && $parameter->var->name === $token->link->property) {
                            $node = $parameter;
                            break;
                        }
                    }
                }

                $target = $this->target($path, $node, $deprecated);
            }
        } else {
            // empty
        }

        // Return
        return $target;
    }

    private function target(string $path, ?Node $node, bool $deprecated): ?LinkTarget {
        if ($node === null) {
            return null;
        }

        $comment    = $node->getDocComment();
        $endLine    = $node->getEndLine();
        $endLine    = $endLine >= 0 ? $endLine : null;
        $startLine  = $comment?->getStartLine() ?? $node->getStartLine();
        $startLine  = $startLine >= 0 ? $startLine : null;
        $deprecated = $deprecated || (new PhpDoc($comment?->getText()))->isDeprecated();

        return new LinkTarget($path, $deprecated, $startLine, $endLine);
    }
}
