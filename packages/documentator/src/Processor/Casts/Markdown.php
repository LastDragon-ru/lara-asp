<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown as MarkdownContract;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Cast;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Resolver;
use Override;

/**
 * @implements Cast<Document>
 */
readonly class Markdown implements Cast {
    public function __construct(
        protected MarkdownContract $markdown,
    ) {
        // empty
    }

    #[Override]
    public function __invoke(Resolver $resolver, File $file): object {
        return $this->markdown->parse($file->content, $file->path);
    }
}
