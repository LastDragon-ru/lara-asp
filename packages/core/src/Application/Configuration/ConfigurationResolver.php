<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Application\Configuration;

use LastDragon_ru\LaraASP\Core\Application\ConfigResolver;
use LastDragon_ru\LaraASP\Core\Application\Resolver;

/**
 * @template TConfiguration of Configuration
 *
 * @extends Resolver<TConfiguration>
 */
abstract class ConfigurationResolver extends Resolver {
    public function __construct(ConfigResolver $config) {
        parent::__construct(
            static function () use ($config): Configuration {
                /** @var TConfiguration $configuration */
                $configuration = $config->getInstance()->get(static::getName());

                return $configuration;
            },
        );
    }

    abstract protected static function getName(): string;

    /**
     * @return TConfiguration
     */
    abstract public static function getDefaultConfig(): Configuration;
}
