<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http\Resources\Scalar;

use Illuminate\Contracts\Routing\Registrar;
use LastDragon_ru\LaraASP\Spa\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Responses\Laravel\Json\OkResponse;
use PHPUnit\Framework\Attributes\CoversClass;

use function urlencode;

/**
 * @internal
 */
#[CoversClass(TrueResource::class)]
final class TrueResourceTest extends TestCase {
    public function testToResponse(): void {
        $this->app()->make(Registrar::class)
            ->get(__METHOD__, static function (): mixed {
                return new TrueResource();
            });

        $this->get(urlencode(__METHOD__))->assertThat(new OkResponse(TrueResource::class));
    }
}
