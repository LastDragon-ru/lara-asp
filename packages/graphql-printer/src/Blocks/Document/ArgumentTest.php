<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\ArgumentNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Argument::class)]
class ArgumentTest extends TestCase {
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
        ArgumentNode $argumentNode,
        ?Type $argumentType,
    ): void {
        $context = new Context($settings, null, null);
        $actual  = (string) (new Argument($context, $level, $used, $argumentNode, $argumentType));

        if ($expected) {
            Parser::argument($actual);
        }

        self::assertEquals($expected, $actual);
    }

    public function testStatistics(): void {
        $context  = new Context(new TestSettings(), null, null);
        $argument = Parser::argument('test: 123');
        $block    = new Argument($context, 0, 0, $argument, Type::int());

        self::assertNotEmpty((string) $block);
        self::assertEquals([], $block->getUsedTypes());
        self::assertEquals([], $block->getUsedDirectives());
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, ArgumentNode, ?Type}>
     */
    public static function dataProviderToString(): array {
        $settings = new TestSettings();

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
                Parser::argument('c: {a: 123}'),
                null,
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
                Parser::argument('c: {a: 123}'),
                null,
            ],
            'directive: TypeFilter => false'   => [
                '',
                $settings
                    ->setTypeFilter(static fn (string $name) => $name !== Type::INT),
                0,
                0,
                Parser::argument('a: 123'),
                Type::int(),
            ],
            'directive: TypeFilter => true'    => [
                'b: "abc"',
                $settings
                    ->setTypeFilter(static fn (string $name) => $name !== Type::INT),
                0,
                0,
                Parser::argument('b: "abc"'),
                Type::string(),
            ],
            'directive: TypeFilter => unknown' => [
                'c: "abc"',
                $settings
                    ->setTypeFilter(static fn (string $name) => $name !== Type::INT),
                0,
                0,
                Parser::argument('c: "abc"'),
                null,
            ],
        ];
    }
    // </editor-fold>
}
