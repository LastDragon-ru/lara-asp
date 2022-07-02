<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Database;

use Exception;
use LastDragon_ru\LaraASP\Testing\Package\TestCase;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Testing\Database\RefreshDatabaseIfEmpty
 */
class RefreshDatabaseIfEmptyTest extends TestCase {
    public function testImpl(): void {
        self::assertNotEmpty($this->app->make(RefreshDatabaseIfEmptyTest_Impl::class));
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class RefreshDatabaseIfEmptyTest_Impl extends BaseTestCase {
    use RefreshDatabaseIfEmpty;

    public function createApplication(): HttpKernelInterface {
        throw new Exception('Not implemented.');
    }
}
