<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Commands;

use LastDragon_ru\LaraASP\Documentator\Metadata\Metadata;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Requirements::class)]
final class RequirementsTest extends TestCase {
    public function testGetMergedVersions(): void {
        $command  = new class() extends Requirements {
            /**
             * @inheritDoc
             */
            #[Override]
            public function getMergedVersions(array $versions, array $merge): array {
                return parent::getMergedVersions($versions, $merge);
            }
        };
        $versions = [
            '0.1.0',
            '0.2.0',
            '0.3.0-beta.0',
            '0.3.0-beta.1',
            '1.0.0',
            '1.1.0',
            '1.2.1-rc.0',
            '1.2.1',
        ];

        self::assertEquals(
            [
                ['0.1.0', '1.2.1'],
            ],
            $command->getMergedVersions($versions, $versions),
        );
        self::assertEquals(
            [
                '0.1.0',
            ],
            $command->getMergedVersions($versions, ['0.1.0']),
        );
        self::assertEquals(
            [
                '0.1.0',
                '0.3.0-beta.1',
                '1.1.0',
            ],
            $command->getMergedVersions($versions, ['0.1.0', '0.3.0-beta.1', '1.1.0']),
        );
        self::assertEquals(
            [
                ['0.1.0', '0.3.0-beta.0'],
                '1.0.0',
                '1.2.1-rc.0',
                '1.2.1',
            ],
            $command->getMergedVersions($versions, ['0.1.0', '0.2.0', '0.3.0-beta.0', '1.0.0', '1.2.1-rc.0', '1.2.1']),
        );
    }

    public function testGetRequirements(): void {
        $metadata = self::getTestData()->content('.json');
        $metadata = $this->app()->make(Serializer::class)->deserialize(Metadata::class, $metadata);
        $packages = [
            'laravel/framework' => 'Laravel',
            'php'               => 'PHP',
        ];
        $command  = new class() extends Requirements {
            /**
             * @inheritDoc
             */
            #[Override]
            public function getRequirements(array $packages, Metadata $metadata): array {
                return parent::getRequirements($packages, $metadata);
            }
        };

        self::assertEquals(
            [
                'laravel/framework' => [
                    '^10.0.0' => [['HEAD', '2.1.0']],
                    '^9.0.0'  => [['HEAD', '0.12.0']],
                    '^8.22.1' => [['3.0.0', '0.2.0']],
                    '^8.0'    => ['0.1.0'],
                ],
                'php'               => [
                    '^8.2'    => [['HEAD', '2.0.0']],
                    '^8.1'    => [['HEAD', '2.0.0']],
                    '^8.0'    => [['4.5.2', '2.0.0']],
                    '^8.0.0'  => [['1.1.2', '0.12.0']],
                    '>=8.0.0' => [['0.11.0', '0.4.0']],
                    '>=7.4.0' => [['0.3.0', '0.1.0']],
                ],
            ],
            $command->getRequirements($packages, $metadata),
        );
    }

    public function testGetPackageRequirements(): void {
        $merge    = [
            'illuminate/*'    => 'laravel/framework',
            'example/package' => 'another/package',
        ];
        $packages = [
            'laravel/framework' => 'Laravel',
            'php'               => 'PHP',
        ];
        $package  = [
            'require' => [
                'php'                  => '^8.1|^8.2|^8.3',
                'ext-mbstring'         => '*',
                'composer/semver'      => '^3.2',
                'laravel/framework'    => '^10.34.0',
                'league/commonmark'    => '^2.4',
                'illuminate/auth'      => '^10 || ^9',
                'illuminate/bus'       => '^9 || ^10',
                'illuminate/contracts' => '^9 || ^10 || ^11',
                'example/package'      => '~1.0.0',
            ],
        ];
        $command  = new class() extends Requirements {
            /**
             * @inheritDoc
             */
            #[Override]
            public function getPackageRequirements(array $require, array $merge, array $package): array {
                return parent::getPackageRequirements(
                    $require,
                    $merge,
                    $package,
                );
            }
        };

        self::assertEquals(
            [
                'laravel/framework' => [
                    '^9',
                    '^10',
                    '^10.34.0',
                    '^11',
                ],
                'php'               => [
                    '^8.1',
                    '^8.2',
                    '^8.3',
                ],
            ],
            $command->getPackageRequirements($packages, $merge, $package),
        );
    }
}
