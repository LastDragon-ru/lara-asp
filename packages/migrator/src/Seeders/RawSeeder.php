<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Seeders;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\Seeder;
use LastDragon_ru\LaraASP\Migrator\Concerns\RawSqlHelper;

abstract class RawSeeder extends Seeder {
    use RawSqlHelper;

    protected SeederService $service;

    public function __construct(SeederService $service) {
        $this->service = $service;
    }

    // <editor-fold desc="Extension">
    // =========================================================================
    protected function getTarget(): ?string {
        return null;
    }

    protected function isSkipped(): bool {
        return false;
    }

    protected function isSeeded(): bool {
        return $this->getTarget()
            ? $this->service->isModelSeeded($this->getTarget())
            : $this->service->isSeeded();
    }
    // </editor-fold>

    // <editor-fold desc="Seed">
    // =========================================================================
    public function run() {
        $this->seeding();

        if ($this->isSkipped()) {
            $this->skipped();

            return;
        }

        if ($this->isSeeded()) {
            $this->skipped('seeded');

            return;
        }

        $this->runRaw();
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    protected function seeding(): void {
        if ($this->command && $this->command->option('class') !== DatabaseSeeder::class) {
            $this->command->getOutput()
                ->writeln("<info>Seeding:</info> {$this->command->option('class')}");
        }
    }

    /**
     * Output "skipped" message.
     *
     * @param string|null $reason
     *
     * @return void
     */
    protected function skipped(string $reason = null): void {
        if ($this->command) {
            $this->command->getOutput()
                ->writeln("<info>         skipped</info>".($reason ? " ({$reason})" : ''));
        }
    }
    // </editor-fold>
}
