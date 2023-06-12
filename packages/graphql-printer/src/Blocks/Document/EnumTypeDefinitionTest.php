<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use Closure;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\EnumType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(EnumTypeDefinition::class)]
#[CoversClass(EnumValuesDefinition::class)]
class EnumTypeDefinitionTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderToString
     *
     * @param EnumType|Closure(): EnumType $type
     */
    public function testToString(
        string $expected,
        Settings $settings,
        int $level,
        int $used,
        Closure|EnumType $type,
    ): void {
        if ($type instanceof Closure) {
            $type = $type();
        }

        $context = new Context($settings, null, null);
        $actual  = (string) (new EnumTypeDefinition($context, $level, $used, $type));

        if ($expected) {
            Parser::enumTypeDefinition($actual);
        }

        self::assertEquals($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, Closure():EnumType|EnumType}>
     */
    public static function dataProviderToString(): array {
        $settings = (new TestSettings())
            ->setNormalizeEnums(false);

        return [
            'enum'       => [
                <<<'STRING'
                enum Test {
                    C
                    B
                    A
                }
                STRING,
                $settings,
                0,
                0,
                new EnumType([
                    'name'   => 'Test',
                    'values' => ['C', 'B', 'A'],
                ]),
            ],
            'indent'     => [
                <<<'STRING'
                enum Test {
                        C
                        B
                        A
                    }
                STRING,
                $settings,
                1,
                0,
                new EnumType([
                    'name'   => 'Test',
                    'values' => ['C', 'B', 'A'],
                ]),
            ],
            'normalized' => [
                <<<'STRING'
                enum Test {
                    A
                    B
                    C
                }
                STRING,
                $settings->setNormalizeEnums(true),
                0,
                0,
                new EnumType([
                    'name'   => 'Test',
                    'values' => ['C', 'B', 'A'],
                ]),
            ],
            'directives' => [
                <<<'STRING'
                enum Test
                @a
                @b
                @c
                {
                    A
                }
                STRING,
                $settings,
                0,
                0,
                new EnumType([
                    'name'              => 'Test',
                    'values'            => ['A'],
                    'astNode'           => Parser::enumTypeDefinition(
                        <<<'STRING'
                        enum Test @a { A }
                        STRING,
                    ),
                    'extensionASTNodes' => [
                        Parser::enumTypeExtension('extend enum Test @b'),
                        Parser::enumTypeExtension('extend enum Test @c'),
                    ],
                ]),
            ],
            'filter'     => [
                '',
                $settings
                    ->setTypeDefinitionFilter(static fn () => false),
                0,
                0,
                new EnumType([
                    'name'   => 'Test',
                    'values' => ['A'],
                ]),
            ],
        ];
    }
    // </editor-fold>
}
