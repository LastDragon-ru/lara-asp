<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Mixins;

use Closure;
use Http\Factory\Guzzle\ResponseFactory;
use Http\Factory\Guzzle\ServerRequestFactory;
use Http\Factory\Guzzle\StreamFactory;
use Http\Factory\Guzzle\UploadedFileFactory;
use Illuminate\Testing\TestResponse;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentType;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCode;
use PHPUnit\Framework\Constraint\Constraint;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;

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

    public function assertThat(): Closure {
        return function (Constraint $constraint, string $message = ''): TestResponse {
            /** @var \Illuminate\Testing\TestResponse $this */
            $ServerRequestFactory = new ServerRequestFactory();
            $uploadedFileFactory  = new UploadedFileFactory();
            $ResponseFactory      = new ResponseFactory();
            $streamFactory        = new StreamFactory();
            $psrFactory           = new PsrHttpFactory($ServerRequestFactory, $streamFactory, $uploadedFileFactory, $ResponseFactory);
            $response             = $psrFactory->createResponse($this->baseResponse);

            Assert::assertThatResponse($response, $constraint, $message);

            return $this;
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

    public function assertStatusCode(): Closure {
        return function (int $statusCode, string $message = ''): TestResponse {
            /** @var \Illuminate\Testing\TestResponse $this */
            Assert::assertThat($this->getStatusCode(), new StatusCode($statusCode), $message);

            return $this;
        };
    }
}
