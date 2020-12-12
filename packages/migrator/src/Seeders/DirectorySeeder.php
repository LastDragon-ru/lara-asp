<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Seeders;

use Composer\Autoload\ClassMapGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use ReflectionClass;
use Symfony\Component\Filesystem\Filesystem;
use function array_filter;
use function array_keys;
use function dirname;
use function is_subclass_of;
use function sort;

/**
 * Directory Seeder. Loads all seeders from specified paths.
 */
abstract class DirectorySeeder extends Seeder {
    /**
     * Paths from which seeders should be loaded (recursive).
     *
     * @var string[]
     */
    protected array      $paths = [];
    protected Filesystem $files;

    public function __construct(Filesystem $files) {
        $this->files = $files;
    }

    // <editor-fold desc="\Illuminate\Database\Seeder">
    // =========================================================================
    public function run(): void {
        $paths = (new Collection($this->paths))
            ->map(function (string $path) {
                return $this->getSeederPath($path);
            })
            ->unique();

        foreach ($paths as $path) {
            $seeders = $this->getSeedersFromPath($path);

            foreach ($seeders as $seeder) {
                $this->call($seeder);
            }
        }
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    protected function getSeederPath(string $path): string {
        if (!$this->files->isAbsolutePath($path)) {
            $path = "{$this->getSeederBasePath()}/{$path}";
        }

        return $path;
    }

    protected function getSeederBasePath(): string {
        $class = new ReflectionClass($this);
        $base  = dirname($class->getFileName());
        $name  = $class->getShortName();

        return "{$base}/{$name}";
    }

    protected function getSeedersFromPath(string $path): array {
        $classes = ClassMapGenerator::createMap($path);
        $classes = array_keys($classes);
        $classes = array_filter($classes, function (string $class): bool {
            return is_subclass_of($class, Seeder::class, true)
                && $class !== static::class;
        });

        sort($classes);

        return $classes;
    }
    // </editor-fold>
}
