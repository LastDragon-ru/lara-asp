<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Routing;

use Illuminate\Support\Facades\Route;
use LastDragon_ru\LaraASP\Spa\Testing\TestCase;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCodes\NotFound;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Spa\Routing\UnresolvedValueException
 */
class UnresolvedValueExceptionTest extends TestCase {
    public function testHttpResponse(): void {
        Route::get(__FUNCTION__, function () {
            throw new UnresolvedValueException(123);
        });

        $this->get(__FUNCTION__)->assertThat(new NotFound());
    }
}
