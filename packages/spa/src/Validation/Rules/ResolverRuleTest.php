<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Validation\Rules;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Routing\Router;
use LastDragon_ru\LaraASP\Spa\Routing\Resolver;
use LastDragon_ru\LaraASP\Spa\Testing\Package\TestCase;
use stdClass;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Spa\Validation\Rules\ResolverRule
 */
class ResolverRuleTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::passes
     */
    public function testPasses(): void {
        $translator = $this->app->make(Translator::class);
        $router     = $this->app->make(Router::class);
        $resolver   = new class($router) extends Resolver {
            /**
             * @inheritDoc
             */
            protected function resolve(mixed $value, array $parameters): mixed {
                return new stdClass();
            }
        };
        $rule       = new ResolverRule($translator, $resolver);

        self::assertTrue($rule->passes('attribute', 'value'));
    }

    /**
     * @covers ::passes
     */
    public function testPassesUnresolved(): void {
        $translator = $this->app->make(Translator::class);
        $router     = $this->app->make(Router::class);
        $resolver   = new class($router) extends Resolver {
            /**
             * @inheritDoc
             */
            protected function resolve(mixed $value, array $parameters): mixed {
                return null;
            }
        };
        $rule       = new ResolverRule($translator, $resolver);

        self::assertFalse($rule->passes('attribute', 'value'));
    }

    /**
     * @covers ::message
     */
    public function testMessage(): void {
        $translator = $this->app->make(Translator::class);
        $router     = $this->app->make(Router::class);
        $resolver   = new class($router) extends Resolver {
            /**
             * @inheritDoc
             */
            protected function resolve(mixed $value, array $parameters): mixed {
                return new stdClass();
            }
        };
        $rule       = new ResolverRule($translator, $resolver);

        self::assertEquals('The :attribute not found.', $rule->message());
    }
    // </editor-fold>
}
