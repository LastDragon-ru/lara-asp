<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Targets;

use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TargetIsNotDirectory;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function basename;

/**
 * @internal
 */
#[CoversClass(DirectoryPath::class)]
final class DirectoryPathTest extends TestCase {
    public function testResolveRelative(): void {
        $dir      = __DIR__;
        $target   = basename($dir);
        $params   = null;
        $context  = new Context($dir, $target, null);
        $resolver = new DirectoryPath();

        self::assertSame(
            Path::normalize($dir),
            $resolver->resolve($context, $params),
        );
    }

    public function testResolveAbsolute(): void {
        $dir      = __DIR__;
        $target   = $dir;
        $params   = null;
        $context  = new Context($dir, $target, null);
        $resolver = new DirectoryPath();

        self::assertSame(
            Path::normalize($dir),
            $resolver->resolve($context, $params),
        );
    }

    public function testResolveNotADirectory(): void {
        $dir      = __DIR__;
        $target   = 'not/a/directory';
        $params   = null;
        $context  = new Context($dir, $target, null);
        $resolver = new DirectoryPath();

        self::expectException(TargetIsNotDirectory::class);
        self::expectExceptionMessage("The `{$target}` is not a directory (in `{$dir}`).");

        $resolver->resolve($context, $params);
    }
}
