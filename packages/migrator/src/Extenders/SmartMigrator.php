<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Extenders;

use Illuminate\Database\Migrations\Migrator;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use Override;
use Symfony\Component\Finder\Finder;

use function is_string;

// todo(migrator): [laravel] [update] \Illuminate\Database\Migrations\Migrator

/**
 * Extends standard migrator.
 *
 * - Nested directories support
 */
class SmartMigrator extends Migrator {
    public static function create(Migrator $migrator): self {
        return new self($migrator->repository, $migrator->resolver, $migrator->files, $migrator->events);
    }

    // <editor-fold desc="\Illuminate\Database\Migrations\Migrator">
    // =========================================================================
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param array<array-key, mixed>|string $paths
     *
     * @return array<array-key, string>
     */
    #[Override]
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
}
