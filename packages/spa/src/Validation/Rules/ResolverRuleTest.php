<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Validation\Rules;

use Illuminate\Container\Container;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Routing\Router;
use LastDragon_ru\LaraASP\Spa\Routing\Resolver;
use LastDragon_ru\LaraASP\Spa\Testing\Package\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

/**
 * @internal
 */
#[CoversClass(ResolverRule::class)]
final class ResolverRuleTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    public function testPasses(): void {
        $translator = Container::getInstance()->make(Translator::class);
        $router     = Container::getInstance()->make(Router::class);
        $resolver   = new class($router) extends Resolver {
            /**
             * @inheritDoc
             */
            #[Override]
            protected function resolve(mixed $value, array $parameters): mixed {
                return new stdClass();
            }
        };
        $rule       = new ResolverRule($translator, $resolver);

        self::assertTrue($rule->passes('attribute', 'value'));
    }

    public function testPassesUnresolved(): void {
        $translator = Container::getInstance()->make(Translator::class);
        $router     = Container::getInstance()->make(Router::class);
        $resolver   = new class($router) extends Resolver {
            /**
             * @inheritDoc
             */
            #[Override]
            protected function resolve(mixed $value, array $parameters): mixed {
                return null;
            }
        };
        $rule       = new ResolverRule($translator, $resolver);

        self::assertFalse($rule->passes('attribute', 'value'));
    }

    public function testMessage(): void {
        $translator = Container::getInstance()->make(Translator::class);
        $router     = Container::getInstance()->make(Router::class);
        $resolver   = new class($router) extends Resolver {
            /**
             * @inheritDoc
             */
            #[Override]
            protected function resolve(mixed $value, array $parameters): mixed {
                return new stdClass();
            }
        };
        $rule       = new ResolverRule($translator, $resolver);

        self::assertEquals('The :attribute not found.', $rule->message());
    }
    // </editor-fold>
}
