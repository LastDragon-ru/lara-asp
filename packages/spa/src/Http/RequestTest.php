<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\Router;
use LastDragon_ru\LaraASP\Spa\Routing\Resolver;
use LastDragon_ru\LaraASP\Spa\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Spa\Validation\Rules\ResolverRule;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Spa\Http\Request
 */
class RequestTest extends TestCase {
    /**
     * @covers ::validated
     */
    public function testValidated(): void {
        $router        = $this->app->make(Router::class);
        $translator    = $this->app->make(Translator::class);
        $resolverRuleA = new ResolverRule($translator, new class($router) extends Resolver {
            /**
             * @inheritdoc
             */
            protected function resolve(mixed $value, array $parameters): mixed {
                return ['a' => $value];
            }
        });
        $resolverRuleB = new ResolverRule($translator, new class($router) extends Resolver {
            /**
             * @inheritdoc
             */
            protected function resolve(mixed $value, array $parameters): mixed {
                return ['b' => $value];
            }
        });
        $rule          = new class() implements Rule {
            /**
             * @inheritdoc
             */
            public function passes($attribute, $value): bool {
                return (bool) $value;
            }

            public function message(): string {
                return static::class;
            }
        };

        $request = new class($rule, $resolverRuleA, $resolverRuleB) extends Request {
            private Rule         $rule;
            private ResolverRule $resolverRuleA;
            private ResolverRule $resolverRuleB;

            public function __construct(Rule $rule, ResolverRule $resolverRuleA, ResolverRule $resolverRuleB) {
                $this->rule          = $rule;
                $this->resolverRuleA = $resolverRuleA;
                $this->resolverRuleB = $resolverRuleB;

                parent::__construct();
            }

            /**
             * @return array<string,mixed>
             */
            public function rules(): array {
                return [
                    'rule'              => ['required', $this->rule],
                    'rule_nullable'     => ['nullable', 'int'],
                    'resolver'          => ['required', $this->resolverRuleA],
                    'resolver_nullable' => ['nullable', $this->resolverRuleA],
                    'resolver_chained'  => ['required', $this->resolverRuleA, $this->resolverRuleB],
                    'array'             => ['required', 'array'],
                    'array.*.rule'      => ['required', $this->rule],
                    'array.*.resolver'  => ['required', $this->resolverRuleA],
                ];
            }
        };

        $request->replace([
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
        ]);

        $request->setContainer($this->app);
        $request->setRedirector($this->app->make(Redirector::class));

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
    }
}
