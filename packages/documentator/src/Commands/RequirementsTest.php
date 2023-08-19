<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Commands;

use LastDragon_ru\LaraASP\Documentator\Metadata\Metadata;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Requirements::class)]
class RequirementsTest extends TestCase {
    public function testGetMergedVersions(): void {
        $command  = new class() extends Requirements {
            /**
             * @inheritDoc
             */
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
        $metadata = $this->app->make(Serializer::class)->deserialize(Metadata::class, $metadata);
        $packages = [
            'laravel/framework' => 'Laravel',
            'php'               => 'PHP',
        ];
        $command  = new class() extends Requirements {
            /**
             * @inheritDoc
             */
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
}
