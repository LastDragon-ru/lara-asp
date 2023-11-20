<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http;

use Illuminate\Container\Container;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\Router;
use LastDragon_ru\LaraASP\Spa\Routing\Resolver;
use LastDragon_ru\LaraASP\Spa\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Spa\Validation\Rules\ResolverRule;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Request::class)]
class RequestTest extends TestCase {
    public function testValidated(): void {
        $router        = Container::getInstance()->make(Router::class);
        $translator    = Container::getInstance()->make(Translator::class);
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
        $rule          = new class() implements Rule {
            /**
             * @inheritDoc
             */
            #[Override]
            public function passes($attribute, $value): bool {
                return (bool) $value;
            }

            #[Override]
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

        $request->setContainer(Container::getInstance());
        $request->setRedirector(Container::getInstance()->make(Redirector::class));

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
