<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Configs;

use Exception;
use Illuminate\Config\Repository;
use InvalidArgumentException;
use LastDragon_ru\LaraASP\Queue\Concerns\Configurable;
use LastDragon_ru\LaraASP\Queue\Contracts\ConfigurableQueueable;
use LastDragon_ru\LaraASP\Queue\QueueableConfigurator;
use LastDragon_ru\LaraASP\Queue\Queueables\CronJob;
use LastDragon_ru\LaraASP\Queue\Queueables\Job;
use LastDragon_ru\LaraASP\Queue\Queueables\Listener;
use LastDragon_ru\LaraASP\Queue\Queueables\Mail;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Queue\Configs\QueueableConfig
 */
class QueueableConfigTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::getQueueClass
     * @dataProvider dataProviderGetQueueClass
     */
    public function testGetQueueClass(string $expected, string $class): void {
        $repository   = new Repository();
        $configurator = new QueueableConfigurator($repository);
        $properties   = [];
        $queueable    = new $class($configurator);
        $config       = new class($repository, $queueable, $properties) extends QueueableConfig {
            public function getQueueClass(): string {
                return parent::getQueueClass();
            }
        };

        $this->assertEquals($expected, $config->getQueueClass());
    }

    /**
     * @covers ::config
     * @dataProvider dataProviderConfig
     *
     * @param array|\Exception $expected
     * @param array            $appConfig
     * @param array            $queueableConfig
     *
     * @return void
     */
    public function testConfig($expected, array $appConfig, array $queueableConfig): void {
        $repository   = new Repository();
        $configurator = new class($repository) extends QueueableConfigurator {
            public function getQueueableProperties(): array {
                return parent::getQueueableProperties();
            }
        };
        $properties   = $configurator->getQueueableProperties();
        $queueable    = new class($configurator, $queueableConfig) extends Job {
            private array $config;

            public function __construct(QueueableConfigurator $configurator, array $config) {
                $this->config = $config;

                parent::__construct($configurator);
            }

            public function getQueueConfig(): array {
                return $this->config;
            }
        };
        $config       = new class($repository, $queueable, $properties) extends QueueableConfig {
            public function config(): array {
                return parent::config();
            }

            public function getApplicationConfig(): string {
                return parent::getApplicationConfig();
            }
        };

        $repository->set($config->getApplicationConfig(), $appConfig);

        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        $this->assertEquals($expected, $config->config());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    public function dataProviderGetQueueClass(): array {
        return [
            [QueueableConfigTest_getQueueClass::class, QueueableConfigTest_getQueueClass::class],
            [QueueableConfigTest_getQueueClass::class, QueueableConfigTest_getQueueClass_Extending::class],
            [QueueableConfigTest_getQueueClass_Overriding::class, QueueableConfigTest_getQueueClass_Overriding::class],
            [QueueableConfigTest_getQueueClass_Job::class, QueueableConfigTest_getQueueClass_Job::class],
            [QueueableConfigTest_getQueueClass_CronJob::class, QueueableConfigTest_getQueueClass_CronJob::class],
            [QueueableConfigTest_getQueueClass_Listener::class, QueueableConfigTest_getQueueClass_Listener::class],
            [QueueableConfigTest_getQueueClass_Mail::class, QueueableConfigTest_getQueueClass_Mail::class],
        ];
    }

    public function dataProviderConfig(): array {
        return [
            'empty'               => [
                [
                    'connection'              => null,
                    'queue'                   => null,
                    'timeout'                 => null,
                    'tries'                   => null,
                    'maxExceptions'           => null,
                    'backoff'                 => null,
                    'deleteWhenMissingModels' => null,
                    'debug'                   => false,
                ],
                [],
                [],
            ],
            'app'                 => [
                [
                    'connection'              => null,
                    'queue'                   => 'app',
                    'timeout'                 => 123,
                    'tries'                   => null,
                    'maxExceptions'           => null,
                    'backoff'                 => null,
                    'deleteWhenMissingModels' => null,
                    'debug'                   => false,
                ],
                [
                    'queue'   => 'app',
                    'timeout' => 123,
                ],
                [],
            ],
            'queueable'           => [
                [
                    'connection'              => null,
                    'queue'                   => 'queueable',
                    'timeout'                 => null,
                    'tries'                   => 123,
                    'maxExceptions'           => null,
                    'backoff'                 => null,
                    'deleteWhenMissingModels' => null,
                    'debug'                   => false,
                ],
                [],
                [
                    'queue' => 'queueable',
                    'tries' => 123,
                ],
            ],
            'app + queueable'     => [
                [
                    'connection'              => null,
                    'queue'                   => 'app',
                    'timeout'                 => 123,
                    'tries'                   => 123,
                    'maxExceptions'           => null,
                    'backoff'                 => null,
                    'deleteWhenMissingModels' => null,
                    'debug'                   => false,
                ],
                [
                    'queue'   => 'app',
                    'timeout' => 123,
                ],
                [
                    'queue' => 'queueable',
                    'tries' => 123,
                ],
            ],
            'app + unknown'       => [
                new InvalidArgumentException('Unknown key.'),
                [
                    'unknown' => 'key',
                ],
                [],
            ],
            'queueable + unknown' => [
                [
                    'connection'              => null,
                    'queue'                   => null,
                    'timeout'                 => null,
                    'tries'                   => null,
                    'maxExceptions'           => null,
                    'backoff'                 => null,
                    'deleteWhenMissingModels' => null,
                    'debug'                   => false,
                    'unknown'                 => 'key',
                ],
                [],
                [
                    'unknown' => 'key',
                ],
            ],
        ];
    }
    // </editor-fold>
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class QueueableConfigTest_getQueueClass implements ConfigurableQueueable {
    use Configurable;
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class QueueableConfigTest_getQueueClass_Extending extends QueueableConfigTest_getQueueClass {
    // empty
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class QueueableConfigTest_getQueueClass_Overriding extends QueueableConfigTest_getQueueClass {
    use Configurable;
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class QueueableConfigTest_getQueueClass_Job extends Job {
    // empty
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class QueueableConfigTest_getQueueClass_CronJob extends CronJob {
    // empty
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class QueueableConfigTest_getQueueClass_Listener extends Listener {
    // empty
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class QueueableConfigTest_getQueueClass_Mail extends Mail {
    // empty
}
