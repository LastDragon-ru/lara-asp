<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Mixins;

use Illuminate\Routing\Route;
use LastDragon_ru\LaraASP\Core\Routing\AcceptValidator;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Core\Mixins\RouteMixin
 */
class RouteMixinTest extends TestCase {
    /**
     * @covers ::accept
     */
    public function testAccept() {
        $route  = new Route([], '', []);
        $method = AcceptValidator::Key;

        $this->assertTrue(Route::hasMacro($method));
        $this->assertInstanceOf(Route::class, $route->{$method}('value'));
        $this->assertEquals(['value'], $route->getAction($method));
    }
}
