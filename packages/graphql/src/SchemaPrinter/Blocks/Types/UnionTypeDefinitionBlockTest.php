<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types;

use Closure;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\UnionType;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Events\Event;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Events\TypeUsed;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\SchemaPrinter\TestSettings;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types\UnionTypeDefinitionBlock
 */
class UnionTypeDefinitionBlockTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::__toString
     * @covers       \LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types\UnionMemberTypesList::__toString
     *
     * @dataProvider dataProviderToString
     */
    public function testToString(
        string $expected,
        Settings $settings,
        int $level,
        int $used,
        UnionType $type,
    ): void {
        $actual = (string) (new UnionTypeDefinitionBlock(new Dispatcher(), $settings, $level, $used, $type));

        Parser::unionTypeDefinition($actual);

        self::assertEquals($expected, $actual);
    }

    /**
     * @covers ::__toString
     */
    public function testToStringEvent(): void {
        $spy        = Mockery::spy(static fn (Event $event) => null);
        $union      = new UnionType([
            'name'  => 'Test',
            'types' => [
                new ObjectType([
                    'name' => 'A',
                ]),
                new ObjectType([
                    'name' => 'B',
                ]),
            ],
        ]);
        $settings   = new TestSettings();
        $dispatcher = new Dispatcher();

        $dispatcher->attach(Closure::fromCallable($spy));

        self::assertNotEmpty(
            (string) (new UnionTypeDefinitionBlock($dispatcher, $settings, 0, 0, $union)),
        );

        $spy
            ->shouldHaveBeenCalled()
            ->withArgs(static function (Event $event): bool {
                return $event instanceof TypeUsed
                    && $event->name === 'A';
            })
            ->once();
        $spy
            ->shouldHaveBeenCalled()
            ->withArgs(static function (Event $event): bool {
                return $event instanceof TypeUsed
                    && $event->name === 'B';
            })
            ->once();
        $spy
            ->shouldHaveBeenCalled()
            ->twice();
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, UnionType}>
     */
    public function dataProviderToString(): array {
        $settings = new TestSettings();

        return [
            'single-line'          => [
                <<<'STRING'
                union Test = C | B | A
                STRING,
                $settings,
                0,
                0,
                new UnionType([
                    'name'  => 'Test',
                    'types' => [
                        new ObjectType([
                            'name' => 'C',
                        ]),
                        new ObjectType([
                            'name' => 'B',
                        ]),
                        new ObjectType([
                            'name' => 'A',
                        ]),
                    ],
                ]),
            ],
            'multiline'            => [
                <<<'STRING'
                union Test =
                    | C
                    | B
                    | A
                STRING,
                $settings,
                0,
                120,
                new UnionType([
                    'name'  => 'Test',
                    'types' => [
                        new ObjectType([
                            'name' => 'C',
                        ]),
                        new ObjectType([
                            'name' => 'B',
                        ]),
                        new ObjectType([
                            'name' => 'A',
                        ]),
                    ],
                ]),
            ],
            'indent single-line'   => [
                <<<'STRING'
                union Test = C | B | A
                STRING,
                $settings,
                1,
                0,
                new UnionType([
                    'name'  => 'Test',
                    'types' => [
                        new ObjectType([
                            'name' => 'C',
                        ]),
                        new ObjectType([
                            'name' => 'B',
                        ]),
                        new ObjectType([
                            'name' => 'A',
                        ]),
                    ],
                ]),
            ],
            'indent multiline'     => [
                <<<'STRING'
                union Test =
                        | C
                        | B
                        | A
                STRING,
                $settings,
                1,
                120,
                new UnionType([
                    'name'  => 'Test',
                    'types' => [
                        new ObjectType([
                            'name' => 'C',
                        ]),
                        new ObjectType([
                            'name' => 'B',
                        ]),
                        new ObjectType([
                            'name' => 'A',
                        ]),
                    ],
                ]),
            ],
            'multiline normalized' => [
                <<<'STRING'
                union Test = A | B | C
                STRING,
                $settings->setNormalizeUnions(true),
                0,
                0,
                new UnionType([
                    'name'  => 'Test',
                    'types' => [
                        new ObjectType([
                            'name' => 'C',
                        ]),
                        new ObjectType([
                            'name' => 'B',
                        ]),
                        new ObjectType([
                            'name' => 'A',
                        ]),
                    ],
                ]),
            ],
        ];
    }
    // </editor-fold>
}
