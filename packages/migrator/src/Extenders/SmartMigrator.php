<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Extenders;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use Illuminate\Database\Migrations\Migrator;
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
     * @inheritdoc
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function getMigrationFiles($paths) {
        if (is_string($paths)) {
            $paths = [$paths];
        }

        foreach ($paths as $path) {
            foreach (Finder::create()->in($path)->directories() as $dir) {
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
