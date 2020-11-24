<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Mixins;

use Closure;
use Illuminate\Testing\TestResponse;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentType;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentTypes\Atom;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentTypes\Gif;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentTypes\Html;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentTypes\Jpeg;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentTypes\Json;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentTypes\Markdown;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentTypes\Pdf;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentTypes\Png;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentTypes\Rss;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentTypes\Svg;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentTypes\Text;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentTypes\Zip;

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

    public function assertIsAtom(): Closure {
        return function (string $message = ''): TestResponse {
            /** @var \Illuminate\Testing\TestResponse $this */
            Assert::assertThat($this->getContentType(), new Atom(), $message);

            return $this;
        };
    }

    public function assertIsGif(): Closure {
        return function (string $message = ''): TestResponse {
            /** @var \Illuminate\Testing\TestResponse $this */
            Assert::assertThat($this->getContentType(), new Gif(), $message);

            return $this;
        };
    }

    public function assertIsHtml(): Closure {
        return function (string $message = ''): TestResponse {
            /** @var \Illuminate\Testing\TestResponse $this */
            Assert::assertThat($this->getContentType(), new Html(), $message);

            return $this;
        };
    }

    public function assertIsJpeg(): Closure {
        return function (string $message = ''): TestResponse {
            /** @var \Illuminate\Testing\TestResponse $this */
            Assert::assertThat($this->getContentType(), new Jpeg(), $message);

            return $this;
        };
    }

    public function assertIsJson(): Closure {
        return function (string $message = ''): TestResponse {
            /** @var \Illuminate\Testing\TestResponse $this */
            Assert::assertThat($this->getContentType(), new Json(), $message);

            return $this;
        };
    }

    public function assertIsMarkdown(): Closure {
        return function (string $message = ''): TestResponse {
            /** @var \Illuminate\Testing\TestResponse $this */
            Assert::assertThat($this->getContentType(), new Markdown(), $message);

            return $this;
        };
    }

    public function assertIsPdf(): Closure {
        return function (string $message = ''): TestResponse {
            /** @var \Illuminate\Testing\TestResponse $this */
            Assert::assertThat($this->getContentType(), new Pdf(), $message);

            return $this;
        };
    }

    public function assertIsPng(): Closure {
        return function (string $message = ''): TestResponse {
            /** @var \Illuminate\Testing\TestResponse $this */
            Assert::assertThat($this->getContentType(), new Png(), $message);

            return $this;
        };
    }

    public function assertIsRss(): Closure {
        return function (string $message = ''): TestResponse {
            /** @var \Illuminate\Testing\TestResponse $this */
            Assert::assertThat($this->getContentType(), new Rss(), $message);

            return $this;
        };
    }

    public function assertIsSvg(): Closure {
        return function (string $message = ''): TestResponse {
            /** @var \Illuminate\Testing\TestResponse $this */
            Assert::assertThat($this->getContentType(), new Svg(), $message);

            return $this;
        };
    }

    public function assertIsText(): Closure {
        return function (string $message = ''): TestResponse {
            /** @var \Illuminate\Testing\TestResponse $this */
            Assert::assertThat($this->getContentType(), new Text(), $message);

            return $this;
        };
    }

    public function assertIsZip(): Closure {
        return function (string $message = ''): TestResponse {
            /** @var \Illuminate\Testing\TestResponse $this */
            Assert::assertThat($this->getContentType(), new Zip(), $message);

            return $this;
        };
    }
}
