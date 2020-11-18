<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions;

use LastDragon_ru\LaraASP\Testing\Constraints\JsonMatchesSchema;
use function json_decode;
use function json_encode;

/**
 * @mixin \PHPUnit\Framework\TestCase
 */
trait JsonAssertions {
    protected function assertJsonMatchesSchema(array $json, array $schema, string $message = ''): void {
        $schema = json_decode(json_encode($schema), false);
        $json   = json_decode(json_encode($json), false);

        $this->assertThat($json, new JsonMatchesSchema($schema), $message);
    }
}
