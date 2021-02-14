<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Seeders;

use Composer\Autoload\ClassMapGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use ReflectionClass;
use Symfony\Component\Filesystem\Filesystem;

use function dirname;
use function is_subclass_of;
use function str_ends_with;

/**
 * Directory Seeder. Loads all seeders from specified paths.
 */
abstract class DirectorySeeder extends Seeder {
    /**
     * Paths from which seeders should be loaded (recursive).
     *
     * @var array<string>
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
        $map     = ClassMapGenerator::createMap($path);
        $classes = (new Collection($map))
            ->filter(function (string $path, string $class) {
                return !str_ends_with($path, 'Test.php')
                    && is_subclass_of($class, Seeder::class, true)
                    && $class !== static::class;
            })
            ->keys()
            ->sort()
            ->all();

        return $classes;
    }
    // </editor-fold>
}
