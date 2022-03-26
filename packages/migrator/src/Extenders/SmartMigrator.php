<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Extenders;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use Illuminate\Database\Migrations\Migrator;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use Symfony\Component\Finder\Finder;

use function is_string;

// TODO [laravel] [update] \Illuminate\Database\Migrations\Migrator

/**
 * Extends standard migrator.
 *
 * - Nested directories support
 */
class SmartMigrator extends Migrator {
    // <editor-fold desc="\Illuminate\Database\Migrations\Migrator">
    // =========================================================================
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param array<mixed>|string $paths
     *
     * @return array<string>
     */
    public function getMigrationFiles($paths): array {
        if (is_string($paths)) {
            $paths = [$paths];
        }

        foreach ($paths as $path) {
            foreach (Finder::create()->in(Cast::toString($path))->directories() as $dir) {
                $paths[] = $dir->getPathname();
            }
        }

        return parent::getMigrationFiles($paths);
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    public static function isAnonymousMigrationsSupported(): bool {
        return InstalledVersions::satisfies(new VersionParser(), 'laravel/framework', '>=8.40.0');
    }
    // </editor-fold>
}
