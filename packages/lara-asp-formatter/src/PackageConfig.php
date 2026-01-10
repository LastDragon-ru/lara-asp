<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter;

use LastDragon_ru\LaraASP\Core\Application\Configuration\ConfigurationResolver;
use LastDragon_ru\LaraASP\Formatter\Config\Config;
use Override;

/**
 * @extends ConfigurationResolver<Config>
 */
class PackageConfig extends ConfigurationResolver {
    #[Override]
    protected static function getName(): string {
        return Package::Name;
    }

    #[Override]
    public static function getDefaultConfig(): Config {
        return new Config();
    }
}
