<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Targets;

use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TargetIsNotFile;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function basename;

/**
 * @internal
 */
#[CoversClass(FilePath::class)]
final class FilePathTest extends TestCase {
    public function testResolveRelative(): void {
        $file     = __FILE__;
        $target   = basename($file);
        $params   = null;
        $context  = new Context($file, $target, null);
        $resolver = new FilePath();

        self::assertSame(
            Path::normalize($file),
            $resolver->resolve($context, $params),
        );
    }

    public function testResolveAbsolute(): void {
        $file     = __FILE__;
        $target   = $file;
        $params   = null;
        $context  = new Context($file, $target, null);
        $resolver = new FilePath();

        self::assertSame(
            Path::normalize($file),
            $resolver->resolve($context, $params),
        );
    }

    public function testResolveNotAFile(): void {
        $file     = __FILE__;
        $target   = 'not/a/file';
        $params   = null;
        $context  = new Context($file, $target, null);
        $resolver = new FilePath();

        self::expectException(TargetIsNotFile::class);
        self::expectExceptionMessage("The `{$target}` is not a file (in `{$file}`).");

        $resolver->resolve($context, $params);
    }
}
