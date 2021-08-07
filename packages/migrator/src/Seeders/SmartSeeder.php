<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Seeders;

use Illuminate\Database\Seeder;

use function is_string;

/**
 * Smart Seeder. Unlike standard seeder checks, and stops seeding if the
 * database already seeded.
 */
abstract class SmartSeeder extends Seeder {
    protected SeederService $service;

    public function __construct(SeederService $service) {
        $this->service = $service;
    }

    // <editor-fold desc="Extension">
    // =========================================================================
    protected function getTarget(): ?string {
        return null;
    }

    protected function isSkipped(): bool|string {
        return $this->isSeeded() ? 'seeded' : false;
    }

    protected function isSeeded(): bool {
        return $this->getTarget()
            ? $this->service->isModelSeeded($this->getTarget())
            : $this->service->isSeeded();
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
        if (isset($this->command)) {
            $this->command->getOutput()
                ->writeln('<comment>         skipped</comment>'.($reason ? " ({$reason})" : ''));
        }
    }
    // </editor-fold>
}
