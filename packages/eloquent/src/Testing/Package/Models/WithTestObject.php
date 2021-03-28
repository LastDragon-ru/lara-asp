<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Testing\Package\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

/**
 * @required {@link \LastDragon_ru\LaraASP\Testing\SetUpTraits}
 *
 * @internal
 */
trait WithTestObject {
    use RefreshDatabase;

    public function setUpWithTestObject(): void {
        $table = (new TestObject())->getTable();

        if (Schema::hasTable($table)) {
            return;
        }

        Schema::create($table, static function ($table): void {
            $table->increments('id');
            $table->string('value', 40)->nullable();
        });
    }
}
