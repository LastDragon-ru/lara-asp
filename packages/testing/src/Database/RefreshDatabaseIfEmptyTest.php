<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Database;

use Exception;
use LastDragon_ru\LaraASP\Testing\Testing\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @deprecated 6.2.0
 * @internal
 */
#[CoversClass(RefreshDatabaseIfEmpty::class)]
final class RefreshDatabaseIfEmptyTest extends TestCase {
    public function testImpl(): void {
        self::assertNotEmpty($this->app()->make(RefreshDatabaseIfEmptyTest_Impl::class, [
            'name' => 'test',
        ]));
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
final class RefreshDatabaseIfEmptyTest_Impl extends TestCase {
    use RefreshDatabaseIfEmpty;

    #[Override]
    public function createApplication(): HttpKernelInterface {
        throw new Exception('Not implemented.');
    }
}
