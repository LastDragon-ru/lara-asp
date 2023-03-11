<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Responses\Laravel\Json;

use LastDragon_ru\LaraASP\Testing\Constraints\Response\Factory;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCodes\NotFound;
use LastDragon_ru\LaraASP\Testing\Package\TestCase;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\Testing\Responses\Laravel\Json\ErrorResponse
 */
class ErrorResponseTest extends TestCase {
    public function testEvaluate(): void {
        $response   = Factory::make($this->getJson(__FUNCTION__));
        $constraint = new ErrorResponse(new NotFound());

        self::assertEquals(true, $constraint->evaluate($response, '', true));
    }
}
