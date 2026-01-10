<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions;

use JsonSerializable;
use LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonMatchesSchema;
use LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonSchema;
use PHPUnit\Framework\Assert;
use SplFileInfo;
use stdClass;

/**
 * @mixin Assert
 */
trait JsonAssertions {
    /**
     * Asserts that JSON matches schema.
     *
     * @param JsonSerializable|SplFileInfo|stdClass|array<array-key, string>|string|int|float|bool|null $json
     */
    public static function assertJsonMatchesSchema(
        JsonSchema $schema,
        JsonSerializable|SplFileInfo|stdClass|array|string|int|float|bool|null $json,
        string $message = '',
    ): void {
        static::assertThat($json, new JsonMatchesSchema($schema), $message);
    }
}
