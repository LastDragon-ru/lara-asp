<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Configs;

use Exception;
use Illuminate\Support\DateFactory;
use InvalidArgumentException;
use LastDragon_ru\LaraASP\Queue\Concerns\WithConfig;
use LastDragon_ru\LaraASP\Queue\Contracts\ConfigurableQueueable;
use LastDragon_ru\LaraASP\Queue\QueueableConfigurator;
use LastDragon_ru\LaraASP\Queue\Queueables\CronJob;
use LastDragon_ru\LaraASP\Queue\Queueables\Job;
use LastDragon_ru\LaraASP\Queue\Queueables\Listener;
use LastDragon_ru\LaraASP\Queue\Queueables\Mail;
use LastDragon_ru\LaraASP\Queue\Testing\Package\TestCase;

use function config;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\Queue\Configs\QueueableConfig
 */
class QueueableConfigTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderGetQueueClass
     *
     * @param class-string<ConfigurableQueueable> $class
     */
    public function testGetQueueClass(string $expected, string $class): void {
        $dateFactory  = new DateFactory();
        $configurator = new QueueableConfigurator($dateFactory);
        $properties   = [];
        $queueable    = new $class($configurator);
        $config       = new class($queueable, $properties) extends QueueableConfig {
            public function getQueueClass(): string {
                return parent::getQueueClass();
            }
        };

        self::assertEquals($expected, $config->getQueueClass());
    }

    /**
     * @dataProvider dataProviderConfig
     *
     * @param array<mixed>|Exception $expected
     * @param array<mixed>           $appConfig
     * @param array<string,mixed>    $queueableConfig
     */
    public function testConfig(array|Exception $expected, array $appConfig, array $queueableConfig): void {
        $dateFactory  = new DateFactory();
        $configurator = new class($dateFactory) extends QueueableConfigurator {
            /**
             * @inheritDoc
             */
            public function getQueueableProperties(): array {
                return parent::getQueueableProperties();
            }
        };
        $properties   = $configurator->getQueueableProperties();
        $queueable    = new class($queueableConfig) extends Job {
            /**
             * @param array<string,mixed> $config
             */
            public function __construct(
                private array $config,
            ) {
                parent::__construct();
            }

            /**
             * @inheritDoc
             */
            public function getQueueConfig(): array {
                return $this->config;
            }
        };
        $config       = new class($queueable, $properties) extends QueueableConfig {
            /**
             * @inheritDoc
             */
            public function config(): array {
                return parent::config();
            }

            public function getApplicationConfig(): string {
                return parent::getApplicationConfig();
            }
        };

        config([
            $config->getApplicationConfig() => $appConfig,
        ]);

        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        self::assertEquals($expected, $config->config());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<mixed>
     */
    public static function dataProviderGetQueueClass(): array {
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

    /**
     * @return array<mixed>
     */
    public static function dataProviderConfig(): array {
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
                    'retryUntil'              => null,
                    'afterCommit'             => null,
                    'delay'                   => null,
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
                    'retryUntil'              => null,
                    'afterCommit'             => null,
                    'delay'                   => null,
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
                    'retryUntil'              => null,
                    'afterCommit'             => null,
                    'delay'                   => null,
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
                    'retryUntil'              => null,
                    'afterCommit'             => null,
                    'delay'                   => null,
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
                    'unknown'                 => 'key',
                    'retryUntil'              => null,
                    'afterCommit'             => null,
                    'delay'                   => null,
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

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class QueueableConfigTest_getQueueClass implements ConfigurableQueueable {
    use WithConfig;

    /**
     * @inheritDoc
     */
    public function getQueueConfig(): array {
        return [];
    }
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

// @phpcs:enable
