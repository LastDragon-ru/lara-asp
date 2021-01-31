<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions;

use LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonMatchesSchema;
use LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonSchema;

/**
 * @mixin \PHPUnit\Framework\Assert
 */
trait JsonAssertions {
    /**
     * Asserts that JSON matches schema.
     *
     * @see \LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonMatchesSchema
     *
     * @param \LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonSchema $schema
     * @param \SplFileInfo|\stdClass|array|string                        $json
     * @param string                                                     $message
     *
     * @return void
     */
    public static function assertJsonMatchesSchema(JsonSchema $schema, $json, string $message = ''): void {
        static::assertThat($json, new JsonMatchesSchema($schema), $message);
    }
}
