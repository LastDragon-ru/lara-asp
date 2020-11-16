<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing;

use function array_filter;
use function array_map;
use function class_basename;
use function class_uses_recursive;
use function method_exists;
use function str_starts_with;

/**
 * Automatically calls `setUp<Trait>`/`tearDown<Trait>` (where `<Trait>` is the
 * short trait name) for traits after Laravel set up. The `@before`/`@after`
 * cannot be used because they may run before the app was created.
 *
 * @mixin \Illuminate\Foundation\Testing\TestCase
 */
trait SetUpTraits {
    private static array $setUpTraits = [];

    /**
     * @beforeClass
     */
    public static function setUpTraitsSetUp(): void {
        static::$setUpTraits = array_filter(array_map(function (string $trait) {
            // Self?
            if ($trait === __TRAIT__) {
                return null;
            }

            // Laravel's?
            if (str_starts_with($trait, 'Illuminate\\Foundation\\Testing\\')) {
                return null;
            }

            // Return short name
            return class_basename($trait);
        }, class_uses_recursive(static::class)));
    }

    /**
     * @afterClass
     */
    public static function setUpTraitsTearDown(): void {
        static::$setUpTraits = [];
    }

    public function setUp(): void {
        // Parent
        parent::setUp();

        // Set traits Up
        foreach (static::$setUpTraits as $trait) {
            if (method_exists($this, "setUp{$trait}")) {
                $this->{"setUp{$trait}"}();
            }
        }
    }

    public function tearDown(): void {
        // Tear traits Down
        foreach (static::$setUpTraits as $trait) {
            if (method_exists($this, "tearDown{$trait}")) {
                $this->{"tearDown{$trait}"}();
            }
        }

        // Parent
        parent::tearDown();
    }
}
