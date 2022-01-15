<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types;

use Closure;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\EnumValueDefinition;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings\DefaultSettings;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types\EnumTypeDefinitionBlock
 */
class EnumTypeDefinitionBlockTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::__toString
     * @covers       \LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types\EnumValuesDefinitionList::__toString
     *
     * @dataProvider dataProviderToString
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

        $actual = (string) (new EnumTypeDefinitionBlock(new Dispatcher(), $settings, $level, $used, $type));

        Parser::enumTypeDefinition($actual);

        self::assertEquals($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, Closure():EnumType|EnumType}>
     */
    public function dataProviderToString(): array {
        return [
            'enum'                       => [
                <<<'STRING'
                enum Test {
                    C
                    B
                    A
                }
                STRING,
                new class() extends DefaultSettings {
                    public function getIndent(): string {
                        return '    ';
                    }
                },
                0,
                0,
                new EnumType([
                    'name'   => 'Test',
                    'values' => ['C', 'B', 'A'],
                ]),
            ],
            'indent'                     => [
                <<<'STRING'
                enum Test {
                        C
                        B
                        A
                    }
                STRING,
                new class() extends DefaultSettings {
                    public function getIndent(): string {
                        return '    ';
                    }
                },
                1,
                0,
                new EnumType([
                    'name'   => 'Test',
                    'values' => ['C', 'B', 'A'],
                ]),
            ],
            'normalized'                 => [
                <<<'STRING'
                enum Test {
                    A
                    B
                    C
                }
                STRING,
                new class() extends DefaultSettings {
                    public function getIndent(): string {
                        return '    ';
                    }

                    public function isNormalizeEnums(): bool {
                        return true;
                    }
                },
                0,
                0,
                new EnumType([
                    'name'   => 'Test',
                    'values' => ['C', 'B', 'A'],
                ]),
            ],
            'directives and description' => [
                <<<'STRING'
                enum Test {
                    C

                    """
                    Description
                    """
                    B
                    @b
                    @a

                    A
                    @deprecated
                }
                STRING,
                new class() extends DefaultSettings {
                    public function getIndent(): string {
                        return '    ';
                    }
                },
                0,
                0,
                static function (): EnumType {
                    $enum = new EnumType([
                        'name'   => 'Test',
                        'values' => ['C', 'B', 'A'],
                    ]);

                    $a = $enum->getValue('A');

                    if ($a instanceof EnumValueDefinition) {
                        $a->deprecationReason = Directive::DEFAULT_DEPRECATION_REASON;
                    }

                    $b = $enum->getValue('B');

                    if ($b instanceof EnumValueDefinition) {
                        $b->astNode     = Parser::enumValueDefinition('A @b @a');
                        $b->description = 'Description';
                    }

                    return $enum;
                },
            ],
        ];
    }
    // </editor-fold>
}
