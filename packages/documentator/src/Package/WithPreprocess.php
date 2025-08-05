<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Package;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference\Node;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use Mockery;

/**
 * @phpstan-require-extends TestCase
 * @internal
 */
trait WithPreprocess {
    use WithProcessor;

    protected function getPreprocessInstructionContext(
        FileSystem $fs,
        File $file,
        ?Document $document = null,
        ?Node $node = null,
    ): Context {
        return new Context(
            $this->getDependencyResolver($fs),
            $file,
            $document ?? Mockery::mock(Document::class),
            $node ?? new Node(),
        );
    }
}
