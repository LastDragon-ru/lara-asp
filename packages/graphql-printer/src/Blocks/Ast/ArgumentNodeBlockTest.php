<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Ast;

use GraphQL\Language\AST\ArgumentNode;
use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\DirectiveLocation;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\DirectiveResolver;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Ast\ArgumentNodeBlock
 */
class ArgumentNodeBlockTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderToString
     */
    public function testToString(
        string $expected,
        Settings $settings,
        int $level,
        int $used,
        Directive $directive,
        DirectiveNode $directiveNode,
        ArgumentNode $argumentNode,
    ): void {
        $resolver = new class($directive) implements DirectiveResolver {
            public function __construct(
                private Directive $directive,
            ) {
                // empty
            }

            public function getDefinition(string $name): ?Directive {
                return $this->directive->name === $name
                    ? $this->directive
                    : null;
            }

            /**
             * @inheritDoc
             */
            public function getDefinitions(): array {
                return [$this->directive];
            }
        };
        $context  = new Context($settings, $resolver, null);
        $actual   = (string) (new ArgumentNodeBlock($context, $level, $used, $directiveNode, $argumentNode));

        if ($expected) {
            Parser::argument($actual);
        }

        self::assertEquals($expected, $actual);
    }

    public function testStatistics(): void {
        $context   = new Context(new TestSettings(), null, null);
        $directive = Parser::directive('@test');
        $argument  = Parser::argument('test: 123');
        $block     = new ArgumentNodeBlock($context, 0, 0, $directive, $argument);

        self::assertNotEmpty((string) $block);
        self::assertEquals([], $block->getUsedTypes());
        self::assertEquals([], $block->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, Directive, DirectiveNode, ArgumentNode}>
     */
    public static function dataProviderToString(): array {
        $settings      = new TestSettings();
        $directive     = new Directive([
            'name'        => 'test',
            'description' => 'Description',
            'locations'   => [
                DirectiveLocation::ARGUMENT_DEFINITION,
            ],
            'args'        => [
                'a' => [
                    'type' => Type::int(),
                ],
                'b' => [
                    'type' => Type::string(),
                ],
            ],
        ]);
        $directiveNode = Parser::directive('@test');

        return [
            'directive: argument'              => [
                <<<'STRING'
                c: {
                        a: 123
                    }
                STRING,
                $settings,
                0,
                0,
                $directive,
                $directiveNode,
                Parser::argument('c: {a: 123}'),
            ],
            'directive: argument (level)'      => [
                <<<'STRING'
                c: {
                            a: 123
                        }
                STRING,
                $settings,
                1,
                0,
                $directive,
                $directiveNode,
                Parser::argument('c: {a: 123}'),
            ],
            'directive: TypeFilter => false'   => [
                '',
                $settings
                    ->setTypeFilter(static fn (string $name) => $name !== Type::INT),
                0,
                0,
                $directive,
                $directiveNode,
                Parser::argument('a: 123'),
            ],
            'directive: TypeFilter => true'    => [
                'b: "abc"',
                $settings
                    ->setTypeFilter(static fn (string $name) => $name !== Type::INT),
                0,
                0,
                $directive,
                $directiveNode,
                Parser::argument('b: "abc"'),
            ],
            'directive: TypeFilter => unknown' => [
                'c: "abc"',
                $settings
                    ->setTypeFilter(static fn (string $name) => $name !== Type::INT),
                0,
                0,
                $directive,
                $directiveNode,
                Parser::argument('c: "abc"'),
            ],
        ];
    }
    // </editor-fold>
}
