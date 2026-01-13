<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Docs\Examples;

use Composer\InstalledVersions;
use LastDragon_ru\PhpUnit\Extensions\Requirements\Attributes\RequiresPackage;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[RequiresPackage('phpunit/phpunit')]
final class RequirementsExtensionTest extends TestCase {
    #[RequiresPackage('phpunit/phpunit', '>=10.0.0')]
    public function testSomething(): void {
        self::assertTrue(InstalledVersions::isInstalled('phpunit/phpunit'));
    }
}
