<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Routing;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use LastDragon_ru\LaraASP\Spa\Testing\Package\TestCase;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Resolver::class)]
final class ResolverTest extends TestCase {
    public function testGet(): void {
        $router   = $this->app()->make(Router::class);
        $resolver = new class($router) extends Resolver {
            /**
             * @inheritDoc
             */
            #[Override]
            protected function resolve(mixed $value, array $parameters): mixed {
                return (object) [
                    'id'         => $value,
                    'parameters' => $parameters,
                ];
            }

            /**
             * @inheritDoc
             */
            #[Override]
            protected function resolveParameters(Request $request = null, Route $route = null): array {
                return [
                    'property' => 'value',
                ];
            }
        };

        $a = $resolver->get(123);
        $b = $resolver->get(123);
        $c = $resolver->get(456);

        self::assertNotNull($a);
        self::assertEquals(123, $a->id);
        self::assertSame($a, $b);
        self::assertNotSame($a, $c);
    }

    public function testGetCached(): void {
        $route    = Mockery::mock(Route::class);
        $request  = Mockery::mock(Request::class);
        $resolver = Mockery::mock(Resolver::class);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();

        $resolver
            ->shouldReceive('resolve')
            ->once()
            ->andReturn('value');

        $resolver->get(123, $request, $route);
        $resolver->get(123, $request, $route);
    }

    public function testGetUnresolvedValue(): void {
        self::expectException(UnresolvedValueException::class);

        $router   = $this->app()->make(Router::class);
        $resolver = new class($router) extends Resolver {
            /**
             * @inheritDoc
             */
            #[Override]
            protected function resolve(mixed $value, array $parameters): mixed {
                return null;
            }
        };

        $resolver->get(123);
    }
}
