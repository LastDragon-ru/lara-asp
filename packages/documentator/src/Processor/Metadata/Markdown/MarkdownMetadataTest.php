<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Markdown;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\FileSystem\Content;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(MarkdownMetadata::class)]
final class MarkdownMetadataTest extends TestCase {
    public function testResolve(): void {
        $markdown = Mockery::mock(Markdown::class);
        $document = Mockery::mock(Document::class);
        $metadata = new MarkdownMetadata($markdown);
        $content  = 'content';
        $path     = new FilePath('path/to/file.md');
        $file     = Mockery::mock(File::class);
        $file
            ->shouldReceive('as')
            ->with(Content::class)
            ->once()
            ->andReturn(new Content($content));
        $file
            ->shouldReceive('getPath')
            ->once()
            ->andReturn($path);
        $markdown
            ->shouldReceive('parse')
            ->with($content, $path)
            ->once()
            ->andReturn($document);

        self::assertSame($document, $metadata->resolve($file, Document::class));
    }
}
