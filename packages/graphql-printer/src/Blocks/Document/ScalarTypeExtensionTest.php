<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\ScalarTypeExtensionNode;
use GraphQL\Language\Parser;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\TestSettings;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(ScalarTypeExtension::class)]
final class ScalarTypeExtensionTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderSerialize')]
    public function testSerialize(
        string $expected,
        Settings $settings,
        int $level,
        int $used,
        ScalarTypeExtensionNode $type,
    ): void {
        $collector = new Collector();
        $context   = new Context($settings, null, null);
        $actual    = (new ScalarTypeExtension($context, $type))->serialize($collector, $level, $used);

        if ($expected !== '') {
            Parser::scalarTypeExtension($actual);
        }

        self::assertSame($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, ScalarTypeExtensionNode}>
     */
    public static function dataProviderSerialize(): array {
        $settings = (new TestSettings())
            ->setPrintDirectives(true);

        return [
            'scalar'            => [
                <<<'GRAPHQL'
                extend scalar Test
                @a
                GRAPHQL,
                $settings,
                0,
                0,
                Parser::scalarTypeExtension(
                    'extend scalar Test @a',
                ),
            ],
            'indent'            => [
                <<<'GRAPHQL'
                extend scalar Test
                    @a
                GRAPHQL,
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
                <<<'GRAPHQL'
                extend scalar Test
                @a
                GRAPHQL,
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
