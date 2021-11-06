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
 * @mixin \PHPUnit\Framework\TestCase
 */
trait SetUpTraits {
    /**
     * @var array<string>
     */
    private static array $setUpTraits = [];

    /**
     * @beforeClass
     */
    public static function setUpTraitsSetUp(): void {
        self::$setUpTraits = array_filter(array_map(static function (string $trait): ?string {
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
        self::$setUpTraits = [];
    }

    protected function setUp(): void {
        // Parent
        parent::setUp();

        // Set traits Up
        foreach (self::$setUpTraits as $trait) {
            if (method_exists($this, "setUp{$trait}")) {
                $this->{"setUp{$trait}"}();
            }
        }
    }

    protected function tearDown(): void {
        // Tear traits Down
        foreach (self::$setUpTraits as $trait) {
            if (method_exists($this, "tearDown{$trait}")) {
                $this->{"tearDown{$trait}"}();
            }
        }

        // Parent
        parent::tearDown();
    }
}
