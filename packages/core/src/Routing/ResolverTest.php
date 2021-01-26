<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Routing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use LastDragon_ru\LaraASP\Testing\Package\TestCase;

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
            protected function resolve($value, array $parameters): ?Model {
                $properties = [
                    'id'         => $value,
                    'parameters' => $parameters,
                ];
                $model      = new class() extends Model {
                    // empty
                };

                return $model->forceFill($properties);
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
        $this->assertEquals(123, $a->getKey());
        $this->assertSame($a, $b);
        $this->assertNotSame($a, $c);
    }
}
