<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links\Traits;

use LastDragon_ru\LaraASP\Documentator\Composer\Package;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Contracts\Link;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(ClassTitle::class)]
final class ClassTitleTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderGetTitle')]
    public function testGetTitle(?string $expected, string $value): void {
        $link = new class($value) implements Link {
            use ClassTitle;

            public function __construct(
                public readonly string $value,
            ) {
                // empty
            }

            /**
             * @inheritDoc
             */
            #[Override]
            public function getSource(Directory $root, File $file, Package $package): array|string|null {
                return null;
            }

            #[Override]
            public function __toString(): string {
                return $this->value;
            }
        };

        self::assertEquals($expected, $link->getTitle());
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
