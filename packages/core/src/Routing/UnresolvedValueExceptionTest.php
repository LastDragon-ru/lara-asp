<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Routing;

use Illuminate\Support\Facades\Route;
use LastDragon_ru\LaraASP\Core\Provider;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCodes\NotFound;
use LastDragon_ru\LaraASP\Testing\Package\TestCase;
use function array_merge;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Core\Routing\UnresolvedValueException
 */
class UnresolvedValueExceptionTest extends TestCase {
    // <editor-fold desc="Prepare">
    // =========================================================================
    /**
     * @inheritdoc
     */
    protected function getPackageProviders($app) {
        return array_merge(parent::getPackageProviders($app), [
            Provider::class,
        ]);
    }
    // </editor-fold>

    // <editor-fold desc="Tests">
    // =========================================================================
    public function testHttpResponse(): void {
        Route::get(__FUNCTION__, function () {
            throw new UnresolvedValueException(123);
        });

        $this->get(__FUNCTION__)->assertThat(new NotFound());
    }
    // </editor-fold>
}
