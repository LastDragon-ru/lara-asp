<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links;

use LastDragon_ru\LaraASP\Documentator\Composer\Package;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(Base::class)]
final class BaseTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderGetTitle')]
    public function testGetTitle(?string $expected, string $value): void {
        $link = new class($value) extends Base {
            /**
             * @inheritDoc
             */
            #[Override]
            public function getSource(Directory $root, File $file, Package $package): array|string|null {
                return null;
            }

            #[Override]
            public function __toString(): string {
                return $this->class;
            }
        };

        self::assertEquals($expected, $link->getTitle());
    }

    public function testGetSource(): void {
        $class    = $this::class;
        $resolved = ['a/b/c.php'];
        $package  = Mockery::mock(Package::class);
        $package
            ->shouldReceive('resolve')
            ->with($class)
            ->once()
            ->andReturn($resolved);
        $root = Mockery::mock(Directory::class);
        $file = Mockery::mock(File::class);
        $link = new class ($class) extends Base {
            #[Override]
            public function __toString(): string {
                return $this->class;
            }
        };

        self::assertEquals($resolved, $link->getSource($root, $file, $package));
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{?string, string}>
     */
    public static function dataProviderGetTitle(): array {
        return [
            'empty' => [null, ''],
            'UN'    => ['Class', 'Class'],
            'QN'    => ['Class::$property', 'App\\Class::$property'],
            'FQN'   => ['Class::method()', '\\App\\Class::method()'],
        ];
    }
    //</editor-fold>
}
