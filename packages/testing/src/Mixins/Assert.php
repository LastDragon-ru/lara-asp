<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Mixins;

use Http\Factory\Guzzle\ResponseFactory;
use Http\Factory\Guzzle\ServerRequestFactory;
use Http\Factory\Guzzle\StreamFactory;
use Http\Factory\Guzzle\UploadedFileFactory;
use Illuminate\Testing\TestResponse;
use LastDragon_ru\LaraASP\Testing\Assertions\JsonAssertions;
use LastDragon_ru\LaraASP\Testing\Assertions\XmlAssertions;
use PHPUnit\Framework\Assert as PHPUnitAssert;
use PHPUnit\Framework\Constraint\Constraint;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;

/**
 * @internal
 */
class Assert extends PHPUnitAssert {
    use XmlAssertions;
    use JsonAssertions;

    /**
     * Asserts that TestResponse satisfies given constraint.
     *
     * @param \Illuminate\Testing\TestResponse         $response
     * @param \PHPUnit\Framework\Constraint\Constraint $constraint
     * @param string                                   $message
     *
     * @return void
     */
    public static function assertThatResponse(TestResponse $response, Constraint $constraint, string $message = ''): void {
        $serverRequestFactory = new ServerRequestFactory();
        $uploadedFileFactory  = new UploadedFileFactory();
        $ResponseFactory      = new ResponseFactory();
        $streamFactory        = new StreamFactory();
        $psrFactory           = new PsrHttpFactory($serverRequestFactory, $streamFactory, $uploadedFileFactory, $ResponseFactory);
        $psrResponse          = $psrFactory->createResponse($response->baseResponse);

        static::assertThat($psrResponse, $constraint, $message);
    }
}
