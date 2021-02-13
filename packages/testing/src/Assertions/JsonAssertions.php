<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions;

use JsonSerializable;
use LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonMatchesSchema;
use LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonSchema;
use SplFileInfo;
use stdClass;

/**
 * @mixin \PHPUnit\Framework\Assert
 */
trait JsonAssertions {
    /**
     * Asserts that JSON matches schema.
     */
    public static function assertJsonMatchesSchema(
        JsonSchema $schema,
        JsonSerializable|SplFileInfo|stdClass|array|string $json,
        string $message = '',
    ): void {
        static::assertThat($json, new JsonMatchesSchema($schema), $message);
    }
}
