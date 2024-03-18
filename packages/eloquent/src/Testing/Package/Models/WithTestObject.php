<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Testing\Package\Models;

use Illuminate\Support\Facades\Schema;
use LastDragon_ru\LaraASP\Eloquent\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;

/**
 * @internal
 *
 * @mixin TestCase
 */
trait WithTestObject {
    /**
     * @internal
     */
    #[Before]
    protected function withTestObjectBefore(): void {
        $this->afterApplicationCreated(static function (): void {
            $table = (new TestObject())->getTable();

            if (Schema::hasTable($table)) {
                return;
            }

            Schema::create($table, static function ($table): void {
                $table->increments('id');
                $table->string('value', 40)->nullable();
            });
        });
    }

    /**
     * @internal
     */
    #[After]
    protected function withTestObjectAfter(): void {
        $this->beforeApplicationDestroyed(static function (): void {
            $table = (new TestObject())->getTable();

            if (Schema::hasTable($table)) {
                return;
            }

            Schema::drop($table);
        });
    }
}
