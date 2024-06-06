<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Exceptions;

use LastDragon_ru\LaraASP\Migrator\PackageException;
use LastDragon_ru\LaraASP\Migrator\Seeders\Seeder;
use Throwable;

use function sprintf;

class DatabaseSeeded extends PackageException {
    /**
     * @param class-string<Seeder> $seeder
     */
    public function __construct(
        protected readonly string $seeder,
        Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Impossible to run `%s` seeder, because the database is not empty.',
                $this->seeder,
            ),
            $previous,
        );
    }

    /**
     * @return class-string<Seeder>
     */
    public function getSeeder(): string {
        return $this->seeder;
    }
}
