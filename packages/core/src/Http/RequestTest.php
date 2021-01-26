<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Http;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\Router;
use LastDragon_ru\LaraASP\Core\Routing\Resolver;
use LastDragon_ru\LaraASP\Core\Validation\Rules\ResolverRule;
use LastDragon_ru\LaraASP\Testing\Package\TestCase;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Core\Http\Request
 */
class RequestTest extends TestCase {
    /**
     * @covers ::validated
     */
    public function testValidated(): void {
        $router        = $this->app->make(Router::class);
        $translator    = $this->app->make(Translator::class);
        $resolverRuleA = new ResolverRule($translator, new class($router) extends Resolver {
            protected function resolve($value, array $parameters) {
                return ['a' => $value];
            }
        });
        $resolverRuleB = new ResolverRule($translator, new class($router) extends Resolver {
            protected function resolve($value, array $parameters) {
                return ['b' => $value];
            }
        });
        $rule          = new class() implements Rule {
            public function passes($attribute, $value) {
                return (bool) $value;
            }

            public function message() {
                return __CLASS__;
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

            public function rules() {
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

        $this->assertEquals([
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
