<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Seeders;

use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder as IlluminateSeeder;

use function is_string;
use function is_subclass_of;

/**
 * Smart Seeder. Unlike standard seeder checks, and stops seeding if the
 * database already seeded.
 *
 * @deprecated %{VERSION} Please use {@see Seeder} instead.
 */
abstract class SmartSeeder extends IlluminateSeeder {
    public function __construct(
        protected readonly DatabaseManager $manager,
        protected readonly SeederService $service,
    ) {
        // empty
    }

    // <editor-fold desc="Extension">
    // =========================================================================
    /**
     * @return class-string<Model>|string|null
     */
    protected function getTarget(): ?string {
        return null;
    }

    protected function isSkipped(): bool|string {
        return $this->isSeeded() ? 'seeded' : false;
    }

    protected function isSeeded(): bool {
        $target = $this->getTarget();
        $seeded = false;

        if (is_string($target) && is_subclass_of($target, Model::class, true)) {
            $seeded = $target::query()->count() > 0;
        } elseif ($target) {
            $seeded = $this->getConnectionInstance()->table($target)->count() > 0;
        } else {
            $seeded = $this->service->isSeeded();
        }

        return $seeded;
    }
    // </editor-fold>

    // <editor-fold desc="Seed">
    // =========================================================================
    public function run(): void {
        $reason = $this->isSkipped();

        if ($reason) {
            if (is_string($reason)) {
                $this->skipped($reason);
            } else {
                $this->skipped();
            }

            return;
        }

        $this->seed();
    }

    abstract public function seed(): void;
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    /**
     * Output "skipped" message.
     */
    protected function skipped(?string $reason = null): void {
        if ($this->command !== null) {
            $this->command->getOutput()
                ->writeln('<comment>         skipped</comment>'.($reason ? " ({$reason})" : ''));
        }
    }

    protected function getConnectionInstance(): Connection {
        return $this->manager->connection();
    }
    // </editor-fold>
}
