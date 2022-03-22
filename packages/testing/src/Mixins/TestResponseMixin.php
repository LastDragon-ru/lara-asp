<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Mixins;

use Closure;
use Illuminate\Testing\TestResponse;
use LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonSchema;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentType;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCode;
use PHPUnit\Framework\Constraint\Constraint;
use SplFileInfo;

/**
 * @internal
 */
class TestResponseMixin {
    /**
     * @return Closure(Constraint, string): TestResponse
     */
    public function assertThat(): Closure {
        return function (Constraint $constraint, string $message = ''): TestResponse {
            /** @var TestResponse $this */
            Assert::assertThatResponse($this, $constraint, $message);

            return $this;
        };
    }

    /**
     * @return Closure(JsonSchema, string): TestResponse
     */
    public function assertJsonMatchesSchema(): Closure {
        return function (JsonSchema $schema, string $message = ''): TestResponse {
            /** @var TestResponse $this */
            Assert::assertJsonMatchesSchema($schema, $this->json(), $message);

            return $this;
        };
    }

    /**
     * @return Closure(string, string): TestResponse
     */
    public function assertContentType(): Closure {
        return function (string $contentType, string $message = ''): TestResponse {
            /** @var TestResponse $this */
            Assert::assertThatResponse($this, new ContentType($contentType), $message);

            return $this;
        };
    }

    /**
     * @return Closure(int, string): TestResponse
     */
    public function assertStatusCode(): Closure {
        return function (int $statusCode, string $message = ''): TestResponse {
            /** @var TestResponse $this */
            Assert::assertThatResponse($this, new StatusCode($statusCode), $message);

            return $this;
        };
    }

    /**
     * @return Closure(SplFileInfo, string): TestResponse
     */
    public function assertXmlMatchesSchema(): Closure {
        return function (SplFileInfo $schema, string $message = ''): TestResponse {
            /** @var TestResponse $this */
            Assert::assertXmlMatchesSchema($schema, (string) $this->getContent(), $message);

            return $this;
        };
    }
}
