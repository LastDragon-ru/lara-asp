<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http\Resources\Scalar;

use Illuminate\Support\Facades\Route;
use LastDragon_ru\LaraASP\Spa\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Responses\Laravel\Json\OkResponse;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(NullResource::class)]
class NullResourceTest extends TestCase {
    public function testToResponse(): void {
        Route::get(__METHOD__, static function (): mixed {
            return new NullResource();
        });

        $this->get(__METHOD__)->assertThat(new OkResponse(NullResource::class));
    }
}
