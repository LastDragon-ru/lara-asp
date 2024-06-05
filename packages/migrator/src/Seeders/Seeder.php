<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Seeders;

use Illuminate\Database\Connection;
use Illuminate\Database\Seeder as IlluminateSeeder;
use LastDragon_ru\LaraASP\Migrator\Exceptions\DatabaseIsSeeded;
use Override;

/**
 * Smart Seeder. Unlike standard seeder, it checks and stops seeding if the
 * database is already seeded.
 */
abstract class Seeder extends IlluminateSeeder {
    public function __construct(
        protected readonly SeederService $service,
    ) {
        // empty
    }

    /**
     * @param array<array-key, mixed> $parameters
     */
    #[Override]
    public function __invoke(array $parameters = []): mixed {
        if ($this->isSeeded()) {
            throw new DatabaseIsSeeded($this::class);
        }

        return parent::__invoke($parameters);
    }

    protected function isSeeded(): bool {
        return $this->service->isSeeded($this->getConnection());
    }

    protected function getConnection(): Connection|string|null {
        return null;
    }
}
