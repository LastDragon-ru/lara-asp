<?php declare(strict_types = 1);

namespace Database\Seeders;

use Composer\Autoload\ClassMapGenerator;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Seeder;
use Illuminate\Filesystem\Filesystem;

class DatabaseSeeder extends Seeder {
    protected Application $app;
    protected Filesystem  $files;
    /**
     * @var string[]
     */
    private array        $paths = [];

    public function __construct(Application $app, Filesystem $files) {
        $this->app   = $app;
        $this->files = $files;
    }

    // <editor-fold desc="\Illuminate\Database\Seeder">
    // =========================================================================
    public function run(): void {
        $paths = [
            ...$this->paths,                        // Custom
            $this->app->databasePath('seeders'),    // Application default
        ];

        foreach ($paths as $path) {
            $seeders = $this->getSeedersFromPath($path);

            foreach ($seeders as $seeder) {
                $this->call($seeder);
            }
        }
    }
    // </editor-fold>

    // <editor-fold desc="Getters / Setters">
    // =========================================================================
    public function addPath(string $path): void {
        if (!in_array($path, $this->paths, true)) {
            $this->paths[] = $path;
        }
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    protected function getSeedersFromPath(string $path): array {
        $classes = ClassMapGenerator::createMap($path);
        $classes = array_keys($classes);

        sort($classes);

        return $classes;
    }
    // </editor-fold>
}
