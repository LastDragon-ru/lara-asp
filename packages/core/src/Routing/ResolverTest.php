<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Routing;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use LastDragon_ru\LaraASP\Testing\Package\TestCase;
use function json_decode;
use function json_encode;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Core\Routing\Resolver
 */
class ResolverTest extends TestCase {
    /**
     * @covers ::get
     */
    public function testGet(): void {
        $router   = $this->app->make(Router::class);
        $resolver = new class($router) extends Resolver {
            protected function resolve($value, array $parameters) {
                return json_decode(json_encode([
                    'id'         => $value,
                    'parameters' => $parameters,
                ]), false);
            }

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
            protected function resolve($value, array $parameters) {
                return null;
            }
        };

        $resolver->get(123);
    }
}
