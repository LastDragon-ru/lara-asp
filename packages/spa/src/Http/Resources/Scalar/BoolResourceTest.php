<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http\Resources\Scalar;

use Illuminate\Container\Container;
use Illuminate\Contracts\Routing\Registrar;
use LastDragon_ru\LaraASP\Spa\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Responses\Laravel\Json\OkResponse;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(BoolResource::class)]
final class BoolResourceTest extends TestCase {
    public function testToResponse(): void {
        Container::getInstance()->make(Registrar::class)
            ->get(__METHOD__, static function (): mixed {
                return new BoolResource(true);
            });

        $this->get(__METHOD__)->assertThat(new OkResponse(BoolResource::class));
    }
}
