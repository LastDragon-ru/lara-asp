<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts\Markdown;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Caster;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\Path\FilePath;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(MarkdownCast::class)]
final class MarkdownCastTest extends TestCase {
    public function testCastTo(): void {
        $filesystem = Mockery::mock(FileSystem::class);
        $caster     = Mockery::mock(Caster::class);
        $markdown   = Mockery::mock(Markdown::class);
        $document   = Mockery::mock(Document::class);
        $content    = 'content';
        $cast       = new MarkdownCast($markdown);
        $path       = new FilePath('/path/to/file.md');
        $file       = Mockery::mock(File::class, [$filesystem, $path, $caster]);
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

        self::assertSame($document, $cast->castTo($file, Document::class));
    }
}
