<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Nodes;

use Closure;
use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Type\Definition\ObjectType;
use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Events\Event;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Events\TypeUsed;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings\DefaultSettings;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Nodes\TypeName
 */
class TypeNameTest extends TestCase {
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
        ObjectType $type,
    ): void {
        $actual = (string) (new TypeName(new Dispatcher(), $settings, $level, $used, $type));

        self::assertEquals($expected, $actual);
    }

    /**
     * @covers ::__toString
     */
    public function testToStringEvent(): void {
        $spy        = Mockery::spy(static fn (Event $event) => null);
        $node       = new ObjectType([
            'name' => 'Test',
        ]);
        $settings   = new DefaultSettings();
        $dispatcher = new Dispatcher();

        $dispatcher->attach(Closure::fromCallable($spy));

        self::assertNotNull(
            (string) (new TypeName($dispatcher, $settings, 0, 0, $node)),
        );

        $spy
            ->shouldHaveBeenCalled()
            ->withArgs(static function (Event $event) use ($node): bool {
                return $event instanceof TypeUsed
                    && $event->name === $node->name;
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
     * @return array<string,array{string, Settings, int, int, DirectiveNode}>
     */
    public function dataProviderToString(): array {
        return [
            'object' => [
                'Test',
                new DefaultSettings(),
                0,
                0,
                new ObjectType([
                    'name' => 'Test',
                ]),
            ],
        ];
    }
    // </editor-fold>
}
