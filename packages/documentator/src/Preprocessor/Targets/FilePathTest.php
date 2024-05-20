<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Targets;

use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TargetIsNotFile;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function sprintf;

/**
 * @internal
 */
#[CoversClass(FilePath::class)]
final class FilePathTest extends TestCase {
    public function testResolveRelative(): void {
        $dir      = new Directory(Path::join(__DIR__), false);
        $root     = new Directory(Path::join(__DIR__, '../..'), false);
        $file     = new File(Path::normalize(__FILE__), false);
        $params   = null;
        $context  = new Context($root, $dir, $file, $file->getName(), null);
        $resolver = new FilePath();

        self::assertSame(
            $file->getPath(),
            $resolver->resolve($context, $params),
        );
    }

    public function testResolveAbsolute(): void {
        $dir      = new Directory(Path::join(__DIR__), false);
        $root     = new Directory(Path::join(__DIR__, '../..'), false);
        $file     = new File(Path::normalize(__FILE__), false);
        $params   = null;
        $context  = new Context($root, $dir, $file, $file->getPath(), null);
        $resolver = new FilePath();

        self::assertSame(
            $file->getPath(),
            $resolver->resolve($context, $params),
        );
    }

    public function testResolveNotAFile(): void {
        $dir      = new Directory(Path::join(__DIR__), false);
        $root     = new Directory(Path::join(__DIR__, '../..'), false);
        $file     = new File(Path::normalize(__FILE__), false);
        $target   = 'not/a/file';
        $params   = null;
        $context  = new Context($root, $dir, $file, $target, null);
        $resolver = new FilePath();

        self::expectException(TargetIsNotFile::class);
        self::expectExceptionMessage(sprintf(
            'The `%s` is not a file (in `%s`).',
            $target,
            $context->file->getRelativePath($context->root),
        ));

        $resolver->resolve($context, $params);
    }
}
