<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL;

use Illuminate\Contracts\Config\Repository;
use LastDragon_ru\LaraASP\GraphQL\Config\Config;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorBetweenDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorEqualDirective;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Definitions\SortByOperatorRandomDirective;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Operators as SortByOperators;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Nuwave\Lighthouse\Schema\Directives\RenameDirective;
use Nuwave\Lighthouse\Validation\ValidateDirective;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Provider::class)]
final class ProviderTest extends TestCase {
    public function testConfig(): void {
        self::assertConfigurationExportable(PackageConfig::class);
    }

    /**
     * @deprecated %{VERSION} Array-base config is deprecated.
     */
    public function testLegacyConfig(): void {
        // Prepare
        $app     = $this->app();
        $config  = $app->make(Repository::class);
        $legacy  = (array) require self::getTestData()->path('~LegacyConfig.php');
        $package = Package::Name;

        $config->set($package, $legacy);

        self::assertIsArray($config->get($package));

        (new Provider($app))->register();

        // Test
        $expected                                            = new Config();
        $expected->searchBy->operators['Date']               = [
            SearchByOperatorEqualDirective::class,
            SearchByOperatorBetweenDirective::class,
        ];
        $expected->searchBy->operators['DateTime']           = ['Date'];
        $expected->sortBy->operators[SortByOperators::Extra] = [
            SortByOperatorRandomDirective::class,
        ];
        $expected->stream->search->name                      = 'custom_where';
        $expected->stream->sort->name                        = 'custom_order';
        $expected->stream->limit->name                       = 'custom_limit';
        $expected->stream->limit->default                    = 5;
        $expected->stream->limit->max                        = 10;
        $expected->stream->offset->name                      = 'custom_offset';
        $expected->builder->allowedDirectives                = [
            RenameDirective::class,
            ValidateDirective::class,
        ];

        self::assertEquals($expected, $config->get($package));
    }
}
