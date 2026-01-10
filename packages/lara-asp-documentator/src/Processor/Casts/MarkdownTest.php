<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown as MarkdownContract;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Resolver;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\Path\FilePath;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Markdown::class)]
final class MarkdownTest extends TestCase {
    public function testInvoke(): void {
        $filesystem = Mockery::mock(FileSystem::class);
        $resolver   = Mockery::mock(Resolver::class);
        $markdown   = Mockery::mock(MarkdownContract::class);
        $document   = Mockery::mock(Document::class);
        $content    = 'content';
        $cast       = new Markdown($markdown);
        $path       = new FilePath('/path/to/file.md');
        $file       = Mockery::mock(File::class, [$filesystem, $path]);
        $filesystem
            ->shouldReceive('read')
            ->with($file)
            ->once()
            ->andReturn($content);
        $markdown
            ->shouldReceive('parse')
            ->with($content, $path)
            ->once()
            ->andReturn($document);

        self::assertSame($document, $cast($resolver, $file));
    }
}
