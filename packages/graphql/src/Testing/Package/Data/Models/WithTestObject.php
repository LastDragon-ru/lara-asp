<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package\Data\Models;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;

/**
 * @internal
 *
 * @phpstan-require-extends TestCase
 */
trait WithTestObject {
    /**
     * @internal
     */
    #[Before]
    protected function initWithTestObject(): void {
        $this->afterApplicationCreated(function (): void {
            $schema = $this->app()->make(Builder::class);
            $table  = (new TestObject())->getTable();

            if ($schema->hasTable($table)) {
                return;
            }

            $schema->create($table, static function (Blueprint $table): void {
                $table->string('id')->primary();
                $table->string('value', 40)->nullable();
            });
        });
    }

    /**
     * @internal
     */
    #[After]
    protected function withTestObjectAfter(): void {
        $this->beforeApplicationDestroyed(function (): void {
            $schema = $this->app()->make(Builder::class);
            $table  = (new TestObject())->getTable();

            $schema->dropIfExists($table);
        });
    }
}
