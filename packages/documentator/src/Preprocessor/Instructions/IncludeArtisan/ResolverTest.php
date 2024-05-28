<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeArtisan;

use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function dirname;
use function sprintf;

/**
 * @internal
 */
#[CoversClass(Resolver::class)]
final class ResolverTest extends TestCase {
    public function testInvoke(): void {
        $root     = new Directory(Path::normalize(__DIR__), false);
        $file     = new File(Path::normalize(__FILE__), false);
        $params   = null;
        $command  = 'artisan:command $directory {$directory} "{$directory}" $file {$file} "{$file}"';
        $context  = new Context($root, $file, $command, $params);
        $instance = $this->app()->make(Resolver::class);

        self::assertEquals(
            sprintf(
                'artisan:command $directory %1$s "%1$s" $file %2$s "%2$s"',
                dirname($file->getPath()),
                $file->getPath(),
            ),
            ($instance)($context, $params),
        );
    }
}
