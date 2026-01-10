<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Mixins;

use Closure;
use Illuminate\Testing\TestResponse;
use LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonSchema;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentType;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCode;
use PHPUnit\Framework\Constraint\Constraint;
use SplFileInfo;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
class TestResponseMixin {
    /**
     * @return Closure(Constraint, string): TestResponse<Response>
     */
    public function assertThat(): Closure {
        return function (Constraint $constraint, string $message = ''): TestResponse {
            /** @var TestResponse<Response> $this */
            Assert::assertThatResponse($this, $constraint, $message);

            return $this;
        };
    }

    /**
     * @return Closure(JsonSchema, string): TestResponse<Response>
     */
    public function assertJsonMatchesSchema(): Closure {
        return function (JsonSchema $schema, string $message = ''): TestResponse {
            /** @var TestResponse<Response> $this */
            Assert::assertJsonMatchesSchema($schema, $this->json(), $message);

            return $this;
        };
    }

    /**
     * @return Closure(string, string): TestResponse<Response>
     */
    public function assertContentType(): Closure {
        return function (string $contentType, string $message = ''): TestResponse {
            /** @var TestResponse<Response> $this */
            Assert::assertThatResponse($this, new ContentType($contentType), $message);

            return $this;
        };
    }

    /**
     * @return Closure(int, string): TestResponse<Response>
     */
    public function assertStatusCode(): Closure {
        return function (int $statusCode, string $message = ''): TestResponse {
            /** @var TestResponse<Response> $this */
            Assert::assertThatResponse($this, new StatusCode($statusCode), $message);

            return $this;
        };
    }

    /**
     * @return Closure(SplFileInfo, string): TestResponse<Response>
     */
    public function assertXmlMatchesSchema(): Closure {
        return function (SplFileInfo $schema, string $message = ''): TestResponse {
            /** @var TestResponse<Response> $this */
            Assert::assertXmlMatchesSchema($schema, (string) $this->getContent(), $message);

            return $this;
        };
    }
}
