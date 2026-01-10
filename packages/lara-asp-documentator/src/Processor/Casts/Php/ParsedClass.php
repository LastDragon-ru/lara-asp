<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts\Php;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Utils\PhpDoc;
use LastDragon_ru\LaraASP\Documentator\Utils\PhpDocumentFactory;
use PhpParser\Node\Stmt\ClassLike;

/**
 * @property-read PhpDoc   $comment
 * @property-read Document $markdown
 */
class ParsedClass {
    private ?PhpDoc   $cComment  = null;
    private ?Document $cMarkdown = null;

    public function __construct(
        protected readonly PhpDocumentFactory $factory,
        public readonly ParsedFile $file,
        public readonly ClassLike $node,
    ) {
        // empty
    }

    /**
     * @deprecated %{VERSION} Will be replaced to property hooks soon.
     */
    public function __isset(string $name): bool {
        return $this->__get($name) !== null;
    }

    /**
     * @deprecated %{VERSION} Will be replaced to property hooks soon.
     */
    public function __get(string $name): mixed {
        return match ($name) {
            'comment'  => $this->cComment  ??= new PhpDoc($this->node->getDocComment()?->getText()),
            'markdown' => $this->cMarkdown ??= ($this->factory)($this->comment, $this->file->path, $this->file->context),
            default    => null,
        };
    }
}
