<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Targets;

use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TargetIsNotDirectory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function basename;
use function sprintf;

/**
 * @internal
 */
#[CoversClass(DirectoryPath::class)]
final class DirectoryPathTest extends TestCase {
    public function testInvokeRelative(): void {
        $dir      = new Directory(Path::join(__DIR__, '..'), false);
        $root     = new Directory(Path::join(__DIR__, '../..'), false);
        $file     = new File(Path::normalize(__FILE__), false);
        $params   = null;
        $context  = new Context($root, $dir, $file, basename(__DIR__), null);
        $resolver = new DirectoryPath();

        self::assertSame(
            $dir->getDirectory(__DIR__)?->getPath(),
            ($resolver)($context, $params),
        );
    }

    public function testInvokeAbsolute(): void {
        $dir      = new Directory(Path::join(__DIR__, '..'), false);
        $root     = new Directory(Path::join(__DIR__, '../..'), false);
        $file     = new File(Path::normalize(__FILE__), false);
        $params   = null;
        $context  = new Context($root, $dir, $file, $dir->getPath(), null);
        $resolver = new DirectoryPath();

        self::assertSame(
            $dir->getPath(),
            ($resolver)($context, $params),
        );
    }

    public function testInvokeNotADirectory(): void {
        $dir      = new Directory(Path::join(__DIR__, '..'), false);
        $root     = new Directory(Path::join(__DIR__, '../..'), false);
        $file     = new File(Path::normalize(__FILE__), false);
        $target   = 'not/a/directory';
        $params   = null;
        $context  = new Context($root, $dir, $file, $target, null);
        $resolver = new DirectoryPath();

        self::expectException(TargetIsNotDirectory::class);
        self::expectExceptionMessage(
            sprintf(
                'The `%s` is not a directory (in `%s`).',
                $target,
                $context->file->getRelativePath($context->root),
            ),
        );

        ($resolver)($context, $params);
    }
}
