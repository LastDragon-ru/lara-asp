<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Migrations;

use Illuminate\Database\Migrations\Migrator as IlluminateMigrator;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\Path\DirectoryPath;
use Override;
use Symfony\Component\Finder\Finder;

use function array_unique;
use function is_string;

// todo(lara-asp-migrator): [laravel] [update] \Illuminate\Database\Migrations\Migrator

/**
 * Extends standard migrator.
 *
 * - Nested directories support
 */
class Migrator extends IlluminateMigrator {
    public static function create(IlluminateMigrator $migrator): self {
        return new self($migrator->repository, $migrator->resolver, $migrator->files, $migrator->events);
    }

    // <editor-fold desc="\Illuminate\Database\Migrations\Migrator">
    // =========================================================================
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @inheritDoc
     * @param array<array-key, mixed>|string $paths
     */
    #[Override]
    public function getMigrationFiles($paths): array {
        if (is_string($paths)) {
            $paths = [$paths];
        }

        foreach ($paths as $path) {
            foreach (Finder::create()->in(Cast::toString($path))->directories() as $dir) {
                $paths[] = (string) (new DirectoryPath($dir->getPathname()))->normalized();
            }
        }

        return parent::getMigrationFiles(array_unique($paths));
    }
    // </editor-fold>
}
