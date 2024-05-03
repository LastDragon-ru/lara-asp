<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Testing\Package\Models;

use LastDragon_ru\LaraASP\Eloquent\Testing\Package\TestCase;
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
    protected function withTestObjectBefore(): void {
        $this->afterApplicationCreated(static function (): void {
            $instance = new TestObject();
            $schema   = $instance->getConnection()->getSchemaBuilder();
            $table    = $instance->getTable();

            if ($schema->hasTable($table)) {
                return;
            }

            $schema->create($table, static function ($table): void {
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
            $instance = new TestObject();
            $schema   = $instance->getConnection()->getSchemaBuilder();
            $table    = $instance->getTable();

            $schema->dropIfExists($table);
        });
    }
}
