<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Routing;

use Illuminate\Support\Facades\Route;
use LastDragon_ru\LaraASP\Core\Provider;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCodes\NotFound;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCodes\Ok;
use LastDragon_ru\LaraASP\Testing\Package\TestCase;
use function array_merge;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Core\Routing\AcceptValidator
 */
class AcceptValidatorTest extends TestCase {
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
    /**
     * @covers ::matches
     *
     * @dataProvider dataProviderMatches
     *
     * @param bool     $expected
     * @param string[] $accepts
     * @param string   $request
     *
     * @return void
     */
    public function testMatches(bool $expected, array $accepts, string $request): void {
        $this->createNestedRoute(__FUNCTION__, $accepts);
        $this
            ->get(__FUNCTION__, [
                'Accept' => $request,
            ])
            ->assertThat($expected ? new Ok() : new NotFound());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    public function dataProviderMatches(): array {
        return [
            'not set'                       => [
                true,
                [],
                'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;v=b3;q=0.9',
            ],
            'json accepted but request not' => [
                false,
                [Accept::Json],
                'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;v=b3;q=0.9',
            ],
            'json accepted '                => [
                true,
                [Accept::Json],
                'text/html,application/xhtml+xml,application/xml;q=0.9,application/json,image/avif,image/webp,image/apng,*/*;v=b3;q=0.9',
            ],
            'any (null)'                    => [
                true,
                [null],
                'text/html,application/xhtml+xml,application/xml;q=0.9,application/json,image/avif,image/webp,image/apng,*/*;v=b3;q=0.9',
            ],
            'any (*/*)'                     => [
                true,
                ['*/*'],
                'text/html,application/xhtml+xml,application/xml;q=0.9,application/json,image/avif,image/webp,image/apng,*/*;v=b3;q=0.9',
            ],
            'text/html'                     => [
                true,
                [Accept::Html],
                'text/html,application/xml;q=0.9,application/json,image/avif,image/webp,image/apng,*/*;v=b3;q=0.9',
            ],
            'application/xhtml+xml'         => [
                true,
                [Accept::Html],
                'application/xhtml+xml,application/xml;q=0.9,application/json,image/avif,image/webp,image/apng,*/*;v=b3;q=0.9',
            ],
            'xml'                           => [
                true,
                ['application/xml'],
                'text/html,application/xhtml+xml,application/xml;q=0.9,application/json,image/avif,image/webp,image/apng,*/*;v=b3;q=0.9',
            ],
            'image'                         => [
                true,
                [Accept::Image],
                'text/html,application/xhtml+xml,application/xml;q=0.9,application/json,image/avif,image/webp,image/apng,*/*;v=b3;q=0.9',
            ],
            'nested replace json'           => [
                false,
                [Accept::Json, 'text/plain'],
                'text/html,application/xhtml+xml,application/xml;q=0.9,application/json,image/avif,image/webp,image/apng,*/*;v=b3;q=0.9',
            ],
            'nested accept any'             => [
                true,
                [Accept::Json, null],
                'text/html,application/xhtml+xml,application/xml;q=0.9,application/json,image/avif,image/webp,image/apng,*/*;v=b3;q=0.9',
            ],
            'nested accept html'            => [
                true,
                [Accept::Json, null, Accept::Html],
                'text/html,application/xhtml+xml,application/xml;q=0.9,application/json,image/avif,image/webp,image/apng,*/*;v=b3;q=0.9',
            ],
        ];
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    protected function createNestedRoute(string $path, array $accepts): void {
        /** @var \Illuminate\Routing\Router $router */
        $router = Route::getFacadeRoot();
        $accept = array_shift($accepts);

        if (empty($accepts)) {
            $router
                ->get($path, function () {
                    return 'ok';
                })
                ->accept($accept);
        } else {
            $router->group([
                AcceptValidator::Key => $accept,
            ], function () use ($path, $accepts): void {
                $this->createNestedRoute($path, $accepts);
            });
        }
    }
    // </editor-fold>
}
