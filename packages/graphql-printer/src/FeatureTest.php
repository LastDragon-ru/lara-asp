<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use LastDragon_ru\GraphQLPrinter\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Feature::class)]
final class FeatureTest extends TestCase {
    public function testAvailable(): void {
        $expected = InstalledVersions::satisfies(new VersionParser(), 'webonyx/graphql-php', '>=15.30.0');
        $actual   = Feature::SchemaDescription->available();

        self::assertSame($expected, $actual);
    }
}
