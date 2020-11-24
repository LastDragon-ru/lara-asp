<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Mixins;

use Closure;
use Illuminate\Testing\TestResponse;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentType;

/**
 * @internal
 */
class TestResponseMixin {
    public function getContentType(): Closure {
        return function (): ?string {
            /** @var \Illuminate\Testing\TestResponse $this */
            return $this->headers->get('Content-Type');
        };
    }

    public function assertJsonMatchesSchema(): Closure {
        return function ($schema, string $message = ''): TestResponse {
            /** @var \Illuminate\Testing\TestResponse $this */
            Assert::assertJsonMatchesSchema($this->json(), $schema, $message);

            return $this;
        };
    }

    public function assertContentType(): Closure {
        return function (string $contentType, string $message = ''): TestResponse {
            /** @var \Illuminate\Testing\TestResponse $this */
            Assert::assertThat($this->getContentType(), new ContentType($contentType), $message);

            return $this;
        };
    }
}
