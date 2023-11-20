<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Routing;

use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use LastDragon_ru\LaraASP\Spa\Testing\Package\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Resolver::class)]
class ResolverTest extends TestCase {
    public function testGet(): void {
        $router   = Container::getInstance()->make(Router::class);
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

    public function testGetUnresolvedValue(): void {
        self::expectException(UnresolvedValueException::class);

        $router   = Container::getInstance()->make(Router::class);
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
