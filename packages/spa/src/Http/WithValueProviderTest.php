<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http;

use Closure;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Routing\Router;
use LastDragon_ru\LaraASP\Spa\Routing\Resolver;
use LastDragon_ru\LaraASP\Spa\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Spa\Validation\Rules\ResolverRule;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(WithValueProvider::class)]
final class WithValueProviderTest extends TestCase {
    public function testValidated(): void {
        $router        = $this->app()->make(Router::class);
        $translator    = $this->app()->make(Translator::class);
        $resolverRuleA = new ResolverRule(
            $translator,
            new class($router) extends Resolver {
                /**
                 * @inheritDoc
                 */
                #[Override]
                protected function resolve(mixed $value, array $parameters): mixed {
                    return ['a' => $value];
                }
            },
        );
        $resolverRuleB = new ResolverRule(
            $translator,
            new class($router) extends Resolver {
                /**
                 * @inheritDoc
                 */
                #[Override]
                protected function resolve(mixed $value, array $parameters): mixed {
                    return ['b' => $value];
                }
            },
        );
        $rule          = new class() implements ValidationRule {
            #[Override]
            public function validate(string $attribute, mixed $value, Closure $fail): void {
                if (!$value) {
                    $fail(static::class);
                }
            }
        };
        $data          = [
            'rule'              => true,
            'rule_nullable'     => null,
            'resolver'          => 1,
            'resolver_nullable' => null,
            'resolver_chained'  => 2,
            'array'             => [
                [
                    'rule'     => true,
                    'resolver' => 3,
                ],
                [
                    'rule'     => true,
                    'resolver' => 4,
                ],
            ],
        ];

        $factory = $this->app()->make(Factory::class);
        $request = new class($factory, $rule, $resolverRuleA, $resolverRuleB, $data) {
            use WithValueProvider;

            /**
             * @param array<string, mixed> $data
             */
            public function __construct(
                private readonly Factory $factory,
                private readonly ValidationRule $rule,
                private readonly ResolverRule $resolverRuleA,
                private readonly ResolverRule $resolverRuleB,
                private readonly array $data,
            ) {
                // empty
            }

            /**
             * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint
             *
             * @return Validator
             */
            #[Override]
            protected function getValidatorInstance() {
                return $this->factory->make($this->data, [
                    'rule'              => ['required', $this->rule],
                    'rule_nullable'     => ['nullable', 'int'],
                    'resolver'          => ['required', $this->resolverRuleA],
                    'resolver_nullable' => ['nullable', $this->resolverRuleA],
                    'resolver_chained'  => ['required', $this->resolverRuleA, $this->resolverRuleB],
                    'array'             => ['required', 'array'],
                    'array.*.rule'      => ['required', $this->rule],
                    'array.*.resolver'  => ['required', $this->resolverRuleA],
                ]);
            }
        };

        self::assertEquals([
            'rule'              => true,
            'rule_nullable'     => null,
            'resolver'          => ['a' => 1],
            'array'             => [
                [
                    'rule'     => true,
                    'resolver' => ['a' => 3],
                ],
                [
                    'rule'     => true,
                    'resolver' => ['a' => 4],
                ],
            ],
            'resolver_nullable' => null,
            'resolver_chained'  => ['b' => 2],
        ], $request->validated());

        self::assertEquals(null, $request->validated('rule_nullable'));
        self::assertEquals(['a' => 1], $request->validated('resolver'));
        self::assertEquals(['a' => 4], $request->validated('array.1.resolver'));
        self::assertEquals(
            [
                [
                    'rule'     => true,
                    'resolver' => ['a' => 3],
                ],
                [
                    'rule'     => true,
                    'resolver' => ['a' => 4],
                ],
            ],
            $request->validated('array'),
        );
    }
}
