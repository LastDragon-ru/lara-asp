<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Seeders;

use Symfony\Component\Filesystem\Filesystem;
use function array_merge;

/**
 * Root Seeder. Also loads seeders which were registered via
 * {@link \LastDragon_ru\LaraASP\Migrator\Seeders\SeederService::loadSeedsFrom()};
 */
abstract class RootSeeder extends DirectorySeeder {
    protected SeederService $service;

    public function __construct(Filesystem $files, SeederService $service) {
        parent::__construct($files);

        $this->service = $service;
        $this->paths   = array_merge($service->getSeedersPaths(), $this->paths);
    }
}
