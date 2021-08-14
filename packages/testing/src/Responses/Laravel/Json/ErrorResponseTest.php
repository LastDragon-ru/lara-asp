<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Responses\Laravel\Json;

use LastDragon_ru\LaraASP\Testing\Constraints\Response\Factory;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCodes\NotFound;
use LastDragon_ru\LaraASP\Testing\Package\TestCase;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Testing\Responses\Laravel\Json\ErrorResponse
 */
class ErrorResponseTest extends TestCase {
    /**
     * @covers ::evaluate
     */
    public function testEvaluate(): void {
        $response   = Factory::make($this->getJson(__FUNCTION__));
        $constraint = new ErrorResponse(new NotFound());

        $this->assertEquals(true, $constraint->evaluate($response, '', true));
    }
}
