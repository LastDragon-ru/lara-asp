<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Testing\Package\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Before;

/**
 * @internal
 *
 * @mixin TestCase
 */
trait WithTestObject {
    use RefreshDatabase;

    /**
     * @internal
     */
    #[Before]
    protected function initWithTestObject(): void {
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
}
