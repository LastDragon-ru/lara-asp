<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Mixins;

use Closure;
use Illuminate\Testing\TestResponse;

/**
 * @internal
 */
class TestResponseMixin {
    public function assertJsonMatchesSchema(): Closure {
        return function ($schema, string $message = ''): TestResponse {
            /** @var \Illuminate\Testing\TestResponse $this */
            Assert::assertJsonMatchesSchema($this->json(), $schema, $message);

            return $this;
        };
    }
}
