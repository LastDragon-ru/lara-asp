<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types;

use Closure;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Events\Event;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Events\TypeUsed;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings\DefaultSettings;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types\TypeBlock
 */
class TypeBlockTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::__toString
     *
     * @dataProvider dataProviderToString
     */
    public function testToString(
        string $expected,
        Settings $settings,
        int $level,
        int $used,
        Type $type,
    ): void {
        $actual = (string) (new TypeBlock(new Dispatcher(), $settings, $level, $used, $type));

        self::assertEquals($expected, $actual);
    }

    /**
     * @covers ::__toString
     */
    public function testToStringEvent(): void {
        $spy        = Mockery::spy(static fn (Event $event) => null);
        $node       = new NonNull(
            new ObjectType([
                'name' => 'Test',
            ])
        );
        $settings   = new DefaultSettings();
        $dispatcher = new Dispatcher();

        $dispatcher->attach(Closure::fromCallable($spy));

        self::assertNotEmpty(
            (string) (new TypeBlock($dispatcher, $settings, 0, 0, $node)),
        );

        $spy
            ->shouldHaveBeenCalled()
            ->withArgs(static function (Event $event) use ($node): bool {
                return $event instanceof TypeUsed
                    && $event->name === $node->getWrappedType(true)->name;
            })
            ->once();
        $spy
            ->shouldHaveBeenCalled()
            ->once();
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, Settings, int, int, Type}>
     */
    public function dataProviderToString(): array {
        return [
            'object'        => [
                'Test',
                new DefaultSettings(),
                0,
                0,
                new ObjectType([
                    'name' => 'Test',
                ]),
            ],
            'non null'      => [
                'Test!',
                new DefaultSettings(),
                0,
                0,
                new NonNull(
                    new ObjectType([
                        'name' => 'Test',
                    ]),
                ),
            ],
            'non null list' => [
                '[Test]!',
                new DefaultSettings(),
                0,
                0,
                new NonNull(
                    new ListOfType(
                        new ObjectType([
                            'name' => 'Test',
                        ]),
                    ),
                ),
            ],
        ];
    }
    // </editor-fold>
}
