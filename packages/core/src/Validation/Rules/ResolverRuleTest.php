<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Validation\Rules;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Routing\Router;
use LastDragon_ru\LaraASP\Core\Provider;
use LastDragon_ru\LaraASP\Core\Routing\Resolver;
use LastDragon_ru\LaraASP\Testing\Package\TestCase;
use stdClass;
use function array_merge;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Core\Validation\Rules\ResolverRule
 */
class ResolverRuleTest extends TestCase {
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
     * @covers ::passes
     */
    public function testPasses(): void {
        $translator = $this->app->make(Translator::class);
        $router     = $this->app->make(Router::class);
        $resolver   = new class($router) extends Resolver {
            protected function resolve($value, array $parameters) {
                return new stdClass();
            }
        };
        $rule       = new ResolverRule($translator, $resolver);

        $this->assertTrue($rule->passes('attribute', 'value'));
    }

    /**
     * @covers ::passes
     */
    public function testPassesUnresolved(): void {
        $translator = $this->app->make(Translator::class);
        $router     = $this->app->make(Router::class);
        $resolver   = new class($router) extends Resolver {
            protected function resolve($value, array $parameters) {
                return null;
            }
        };
        $rule       = new ResolverRule($translator, $resolver);

        $this->assertFalse($rule->passes('attribute', 'value'));
    }

    /**
     * @covers ::message
     */
    public function testMessage() {
        $translator = $this->app->make(Translator::class);
        $router     = $this->app->make(Router::class);
        $resolver   = new class($router) extends Resolver {
            protected function resolve($value, array $parameters) {
                return new stdClass();
            }
        };
        $rule       = new ResolverRule($translator, $resolver);

        $this->assertEquals('The :attribute not found.', $rule->message());
    }
    // </editor-fold>
}
