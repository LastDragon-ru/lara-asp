<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

use function is_string;
use function is_subclass_of;

/**
 * Smart Seeder. Unlike standard seeder checks, and stops seeding if the
 * database already seeded.
 */
abstract class SmartSeeder extends Seeder {
    public function __construct(
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
            $seeded = $this->service->isModelSeeded($target);
        } elseif ($target) {
            $seeded = $this->service->isTableSeeded($target);
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
    protected function skipped(string $reason = null): void {
        if ($this->command !== null) {
            $this->command->getOutput()
                ->writeln('<comment>         skipped</comment>'.($reason ? " ({$reason})" : ''));
        }
    }
    // </editor-fold>
}
