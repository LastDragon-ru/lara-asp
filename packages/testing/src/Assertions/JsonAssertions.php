<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions;

use LastDragon_ru\LaraASP\Testing\Constraints\JsonMatchesSchema;
use function json_decode;
use function json_encode;

/**
 * @mixin \PHPUnit\Framework\Assert
 */
trait JsonAssertions {
    /**
     * @param array|\stdClass $json
     * @param array|\stdClass $schema
     * @param string          $message
     *
     * @return void
     */
    public static function assertJsonMatchesSchema($json, $schema, string $message = ''): void {
        $schema = json_decode(json_encode($schema), false);
        $json   = json_decode(json_encode($json), false);

        static::assertThat($json, new JsonMatchesSchema($schema), $message);
    }
}
