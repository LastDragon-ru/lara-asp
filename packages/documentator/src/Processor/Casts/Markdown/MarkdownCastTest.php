<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts\Markdown;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Caster;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\FileSystem\Content;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(MarkdownCast::class)]
final class MarkdownCastTest extends TestCase {
    public function testCastTo(): void {
        $markdown = Mockery::mock(Markdown::class);
        $document = Mockery::mock(Document::class);
        $content  = 'content';
        $cast     = new MarkdownCast($markdown);
        $path     = new FilePath('/path/to/file.md');
        $file     = Mockery::mock(File::class, [$path, Mockery::mock(Caster::class)]);
        $file
            ->shouldReceive('as')
            ->with(Content::class)
            ->once()
            ->andReturn(new Content($content));
        $markdown
            ->shouldReceive('parse')
            ->with($content, $path)
            ->once()
            ->andReturn($document);

        self::assertSame($document, $cast->castTo($file, Document::class));
    }
}
