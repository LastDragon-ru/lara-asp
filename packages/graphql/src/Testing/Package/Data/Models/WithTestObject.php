<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package\Data\Models;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
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

            Schema::create($table, static function (Blueprint $table): void {
                $table->string('id')->primary();
                $table->string('value', 40)->nullable();
            });
        });
    }
}
