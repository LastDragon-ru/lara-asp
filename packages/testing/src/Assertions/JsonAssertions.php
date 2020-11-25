<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions;

use LastDragon_ru\LaraASP\Testing\Constraints\JsonMatchesSchema;

/**
 * @mixin \PHPUnit\Framework\Assert
 */
trait JsonAssertions {
    /**
     * @param \SplFileInfo|\stdClass|array|string $json
     * @param \SplFileInfo|\stdClass|array|string $schema
     * @param string                              $message
     *
     * @return void
     */
    public static function assertJsonMatchesSchema($json, $schema, string $message = ''): void {
        $json   = Loader::loadJson($json);
        $schema = Loader::loadJson($schema);

        static::assertThat($json, new JsonMatchesSchema($schema), $message);
    }
}
