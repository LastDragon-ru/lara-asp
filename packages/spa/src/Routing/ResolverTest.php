<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Routing;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use LastDragon_ru\LaraASP\Spa\Testing\TestCase;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Spa\Routing\Resolver
 */
class ResolverTest extends TestCase {
    /**
     * @covers ::get
     */
    public function testGet(): void {
        $router   = $this->app->make(Router::class);
        $resolver = new class($router) extends Resolver {
            /**
             * @inheritdoc
             */
            protected function resolve(mixed $value, array $parameters): mixed {
                return (object) [
                    'id'         => $value,
                    'parameters' => $parameters,
                ];
            }

            /**
             * @inheritdoc
             */
            protected function resolveParameters(Request $request = null, Route $route = null): array {
                return [
                    'property' => 'value',
                ];
            }
        };

        $a = $resolver->get(123);
        $b = $resolver->get(123);
        $c = $resolver->get(456);

        $this->assertNotNull($a);
        $this->assertEquals(123, $a->id);
        $this->assertSame($a, $b);
        $this->assertNotSame($a, $c);
    }

    /**
     * @covers ::get
     */
    public function testGetUnresolvedValue(): void {
        $this->expectException(UnresolvedValueException::class);

        $router   = $this->app->make(Router::class);
        $resolver = new class($router) extends Resolver {
            /**
             * @inheritdoc
             */
            protected function resolve(mixed $value, array $parameters): mixed {
                return null;
            }
        };

        $resolver->get(123);
    }
}
