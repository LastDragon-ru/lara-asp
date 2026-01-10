<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Routing;

use Illuminate\Contracts\Routing\Registrar;
use LastDragon_ru\LaraASP\Spa\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCodes\NotFound;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(UnresolvedValueException::class)]
final class UnresolvedValueExceptionTest extends TestCase {
    public function testHttpResponse(): void {
        $this->app()->make(Registrar::class)
            ->get(__FUNCTION__, static function (): void {
                throw new UnresolvedValueException(123);
            });

        $this->get(__FUNCTION__)->assertThat(new NotFound());
    }
}
