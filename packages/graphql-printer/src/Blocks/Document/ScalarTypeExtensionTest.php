<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\ScalarTypeExtensionNode;
use GraphQL\Language\Parser;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(ScalarTypeExtension::class)]
class ScalarTypeExtensionTest extends TestCase {
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
        ScalarTypeExtensionNode $type,
    ): void {
        $context = new Context($settings, null, null);
        $actual  = (string) (new ScalarTypeExtension($context, $level, $used, $type));

        if ($expected) {
            Parser::scalarTypeExtension($actual);
        }

        self::assertEquals($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, ScalarTypeExtensionNode}>
     */
    public static function dataProviderToString(): array {
        $settings = (new TestSettings())
            ->setPrintDirectives(true);

        return [
            'scalar'            => [
                <<<'STRING'
                extend scalar Test
                @a
                STRING,
                $settings,
                0,
                0,
                Parser::scalarTypeExtension(
                    'extend scalar Test @a',
                ),
            ],
            'indent'            => [
                <<<'STRING'
                extend scalar Test
                    @a
                STRING,
                $settings,
                1,
                0,
                Parser::scalarTypeExtension(
                    'extend scalar Test @a',
                ),
            ],
            'filter: type'      => [
                '',
                $settings
                    ->setTypeDefinitionFilter(static fn () => false),
                0,
                0,
                Parser::scalarTypeExtension(
                    'extend scalar Test @a',
                ),
            ],
            'filter: directive' => [
                <<<'STRING'
                extend scalar Test
                @a
                STRING,
                $settings
                    ->setDirectiveFilter(static function (string $directive): bool {
                        return $directive !== 'b';
                    }),
                0,
                0,
                Parser::scalarTypeExtension(
                    'extend scalar Test @a @b',
                ),
            ],
        ];
    }
    // </editor-fold>
}
