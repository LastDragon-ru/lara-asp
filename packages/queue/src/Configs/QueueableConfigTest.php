<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Configs;

use Exception;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Support\DateFactory;
use InvalidArgumentException;
use LastDragon_ru\LaraASP\Queue\Concerns\Configurable;
use LastDragon_ru\LaraASP\Queue\Concerns\WithConfig;
use LastDragon_ru\LaraASP\Queue\Contracts\ConfigurableQueueable;
use LastDragon_ru\LaraASP\Queue\QueueableConfigurator;
use LastDragon_ru\LaraASP\Queue\Queueables\CronJob;
use LastDragon_ru\LaraASP\Queue\Queueables\Job;
use LastDragon_ru\LaraASP\Queue\Queueables\Listener;
use LastDragon_ru\LaraASP\Queue\Queueables\Mail;
use PHPUnit\Framework\TestCase;
use stdClass;

use function get_class;

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
        $container    = Container::getInstance();
        $repository   = new Repository();
        $dateFactory  = new DateFactory();
        $configurator = new QueueableConfigurator($container, $repository, $dateFactory);
        $properties   = [];
        $queueable    = new $class($configurator);
        $config       = new class($container, $repository, $queueable, $properties) extends QueueableConfig {
            public function getQueueClass(): string {
                return parent::getQueueClass();
            }
        };

        $this->assertEquals($expected, $config->getQueueClass());
    }

    /**
     * @covers ::config
     * @dataProvider dataProviderConfig
     */
    public function testConfig(array|Exception $expected, array $appConfig, array $queueableConfig): void {
        $container    = Container::getInstance();
        $repository   = new Repository();
        $dateFactory  = new DateFactory();
        $configurator = new class($container, $repository, $dateFactory) extends QueueableConfigurator {
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
        $config       = new class($container, $repository, $queueable, $properties) extends QueueableConfig {
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

    /**
     * @covers ::config
     */
    public function testConfigInjectionIntoGetQueueConfig(): void {
        $container    = Container::getInstance();
        $repository   = new Repository();
        $dateFactory  = new DateFactory();
        $configurator = new QueueableConfigurator($container, $repository, $dateFactory);
        $queueable    = new class($configurator) extends Job {
            public function getQueueConfig(stdClass $injected = null): array {
                return ['injected' => get_class($injected)];
            }
        };

        $this->assertEquals(stdClass::class, $configurator->config($queueable)->get('injected'));
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
                    'retryUntil'              => null,
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
                    'retryUntil'              => null,
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
                    'retryUntil'              => null,
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
                    'retryUntil'              => null,
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
                new InvalidArgumentException('Unknown key `unknown`.'),
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
                    'retryUntil'              => null,
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
    use Configurable, WithConfig;
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
    use WithConfig;
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
