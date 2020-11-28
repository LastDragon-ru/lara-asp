<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Testing\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

/**
 * @required {@link \LastDragon_ru\LaraASP\Testing\SetUpTraits}
 *
 * @internal
 */
trait TestObjectTrait {
    use RefreshDatabase;

    public function setUpTestObjectTrait() {
        $table = (new TestObject())->getTable();

        if (Schema::hasTable($table)) {
            return;
        }

        Schema::create($table, function ($table) {
            $table->increments('id');
            $table->string('value', 40);
        });
    }
}
