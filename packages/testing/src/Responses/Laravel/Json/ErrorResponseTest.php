<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Responses\Laravel\Json;

use LastDragon_ru\LaraASP\Testing\Constraints\Response\Factory;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCodes\NotFound;
use LastDragon_ru\LaraASP\Testing\Testing\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(ErrorResponse::class)]
final class ErrorResponseTest extends TestCase {
    public function testEvaluate(): void {
        $response   = Factory::make($this->getJson(__FUNCTION__));
        $constraint = new ErrorResponse(new NotFound());

        self::assertEquals(true, $constraint->evaluate($response, '', true));
    }
}
