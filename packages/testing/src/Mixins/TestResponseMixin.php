<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Mixins;

use Closure;
use Http\Factory\Guzzle\ResponseFactory;
use Http\Factory\Guzzle\ServerRequestFactory;
use Http\Factory\Guzzle\StreamFactory;
use Http\Factory\Guzzle\UploadedFileFactory;
use Illuminate\Testing\TestResponse;
use LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonSchema;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentType;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCode;
use PHPUnit\Framework\Constraint\Constraint;
use Psr\Http\Message\ResponseInterface;
use SplFileInfo;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;

/**
 * @internal
 */
class TestResponseMixin {
    public function getContentType(): Closure {
        return function (): ?string {
            /** @var TestResponse $this */
            return $this->headers->get('Content-Type');
        };
    }

    public function toPsrResponse(): Closure {
        return function (): ResponseInterface {
            // Some responses (eg StreamedResponse) should be read only
            // one time, so we should use a cloned response and cache the
            // created PSR response (to avoid double code execution).

            /** @var TestResponse $this */
            if (!isset($this->psrResponse)) {
                /* @phpstan-ignore-next-line `psrResponse` is our property only */
                $this->psrResponse = (new PsrHttpFactory(
                    new ServerRequestFactory(),
                    new StreamFactory(),
                    new UploadedFileFactory(),
                    new ResponseFactory(),
                ))->createResponse(clone $this->baseResponse);
            }

            return $this->psrResponse;
        };
    }

    public function assertThat(): Closure {
        return function (Constraint $constraint, string $message = ''): TestResponse {
            /** @var TestResponse $this */
            Assert::assertThatResponse($this, $constraint, $message);

            return $this;
        };
    }

    public function assertJsonMatchesSchema(): Closure {
        return function (JsonSchema $schema, string $message = ''): TestResponse {
            /** @var TestResponse $this */
            Assert::assertJsonMatchesSchema($schema, $this->json(), $message);

            return $this;
        };
    }

    public function assertContentType(): Closure {
        return function (string $contentType, string $message = ''): TestResponse {
            /** @var TestResponse $this */
            Assert::assertThatResponse($this, new ContentType($contentType), $message);

            return $this;
        };
    }

    public function assertStatusCode(): Closure {
        return function (int $statusCode, string $message = ''): TestResponse {
            /** @var TestResponse $this */
            Assert::assertThatResponse($this, new StatusCode($statusCode), $message);

            return $this;
        };
    }

    public function assertXmlMatchesSchema(): Closure {
        return function (SplFileInfo $schema, string $message = ''): TestResponse {
            /** @var TestResponse $this */
            Assert::assertXmlMatchesSchema($schema, (string) $this->getContent(), $message);

            return $this;
        };
    }
}
