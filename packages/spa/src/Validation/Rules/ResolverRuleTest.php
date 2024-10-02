<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Validation\Rules;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\Validation\Factory;
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
    public function testRule(): void {
        $translator = $this->app()->make(Translator::class);
        $router     = $this->app()->make(Router::class);
        $resolver   = new class($router) extends Resolver {
            /**
             * @inheritDoc
             */
            #[Override]
            protected function resolve(mixed $value, array $parameters): mixed {
                return $value !== false ? new stdClass() : null;
            }
        };
        $rule       = new ResolverRule($translator, $resolver);
        $factory    = $this->app()->make(Factory::class);
        $validator  = $factory->make(
            [
                'a' => true,
                'b' => false,
            ],
            [
                'a' => $rule,
                'b' => $rule,
            ],
        );

        self::assertTrue($validator->fails());
        self::assertEquals(
            [
                'b' => [
                    'The b not found.',
                ],
            ],
            $validator->errors()->toArray(),
        );
    }

    public function testIsValid(): void {
        $translator = $this->app()->make(Translator::class);
        $router     = $this->app()->make(Router::class);
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

        self::assertTrue($rule->isValid('attribute', 'value'));
    }

    public function testIsValidUnresolved(): void {
        $translator = $this->app()->make(Translator::class);
        $router     = $this->app()->make(Router::class);
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

        self::assertFalse($rule->isValid('attribute', 'value'));
    }
    // </editor-fold>
}
