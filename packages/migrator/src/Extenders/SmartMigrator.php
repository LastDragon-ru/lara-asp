<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Extenders;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

// TODO [laravel] [update] \Illuminate\Database\Migrations\Migrator

/**
 * Extends standard migrator.
 *
 * - Dependency Injection for migrations
 * - Nested directories support
 */
class SmartMigrator extends Migrator {
    protected ?Container $container;

    public function __construct(
        MigrationRepositoryInterface $repository,
        ConnectionResolverInterface $resolver,
        Filesystem $files,
        Dispatcher $dispatcher = null,
        Container $container = null
    ) {
        parent::__construct($repository, $resolver, $files, $dispatcher);

        $this->container = $container;
    }

    // <editor-fold desc="\Illuminate\Database\Migrations\Migrator">
    // =========================================================================
    public function resolve($file) {
        $class    = Str::studly(implode('_', array_slice(explode('_', $file), 4)));
        $instance = $this->container
            ? $this->container->make($class)
            : new $class();

        return $instance;
    }

    /**
     * @inheritdoc
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function getMigrationFiles($paths) {
        foreach ($paths as $path) {
            foreach (Finder::create()->in($path)->directories() as $dir) {
                $paths[] = $dir->getPathname();
            }
        }

        return parent::getMigrationFiles($paths);
    }
    // </editor-fold>
}
