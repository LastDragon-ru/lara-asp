<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions;

use LastDragon_ru\LaraASP\Testing\Constraints\JsonMatchesSchema;

/**
 * @mixin \PHPUnit\Framework\Assert
 */
trait JsonAssertions {
    /**
     * Asserts that JSON matches schema.
     *
     * @see \LastDragon_ru\LaraASP\Testing\Constraints\JsonMatchesSchema
     *
     * @param \SplFileInfo|\stdClass|array|string $json
     * @param \SplFileInfo|\stdClass|array|string $schema
     * @param string                              $message
     *
     * @return void
     */
    public static function assertJsonMatchesSchema($json, $schema, string $message = ''): void {
        $json   = Args::getJson($json) ?? Args::invalidJson();
        $schema = Args::getJson($schema) ?? Args::invalidJson();

        static::assertThat($json, new JsonMatchesSchema($schema), $message);
    }
}
