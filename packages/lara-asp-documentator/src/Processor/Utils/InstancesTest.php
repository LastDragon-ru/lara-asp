<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Utils;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Container;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

use function iterator_to_array;

use const PHP_INT_MAX;

/**
 * @internal
 */
#[CoversClass(Instances::class)]
final class InstancesTest extends TestCase {
    public function testTags(): void {
        $container = Mockery::mock(Container::class);
        $instances = new InstancesTest__Instances($container, InstancesOrder::Desc);
        $aInstance = new class() extends stdClass {
            // empty
        };
        $bInstance = new class() extends stdClass {
            // empty
        };

        $instances->add($aInstance, ['aa', 'ab'], 200);
        $instances->add($bInstance, ['b', InstancesTest__Enum::B], 100);

        self::assertEquals(['aa', 'ab', 'b', InstancesTest__Enum::B], $instances->tags());
    }

    public function testClasses(): void {
        $container = Mockery::mock(Container::class);
        $instances = new InstancesTest__Instances($container, InstancesOrder::Asc);
        $aInstance = new class() extends stdClass {
            // empty
        };
        $bInstance = new class() extends stdClass {
            // empty
        };
        $cInstance = new class() extends stdClass {
            // empty
        };

        $instances->add($aInstance, ['a'], 200);
        $instances->add($bInstance, ['b'], 100);
        $instances->add($cInstance::class, ['c']);

        self::assertEquals(
            [
                $bInstance::class,
                $aInstance::class,
                $cInstance::class,
            ],
            $instances->classes(),
        );
    }

    public function testIs(): void {
        $container = Mockery::mock(Container::class);
        $instances = new InstancesTest__Instances($container, InstancesOrder::Desc);
        $aInstance = new class() extends stdClass {
            // empty
        };
        $bInstance = new class() extends stdClass {
            // empty
        };

        self::assertFalse($instances->is($aInstance::class));
        self::assertFalse($instances->is($bInstance::class));

        $instances->add($aInstance, ['a']);
        $instances->add($bInstance, ['b']);

        self::assertTrue($instances->is($aInstance::class));
        self::assertTrue($instances->is($bInstance::class));
        self::assertTrue($instances->is($aInstance));
        self::assertTrue($instances->is($bInstance));
    }

    public function testHas(): void {
        $container = Mockery::mock(Container::class);
        $instances = new InstancesTest__Instances($container, InstancesOrder::Desc);
        $aInstance = new class() extends stdClass {
            // empty
        };
        $bInstance = new class() extends stdClass {
            // empty
        };

        self::assertFalse($instances->has());

        $instances->add($aInstance, ['aa', 'ab']);
        $instances->add($bInstance, ['b', InstancesTest__Enum::B]);

        self::assertTrue($instances->has());

        self::assertTrue($instances->has('aa'));
        self::assertTrue($instances->has('ab'));
        self::assertTrue($instances->has('b'));
        self::assertTrue($instances->has('c', 'aa'));
        self::assertTrue($instances->has(InstancesTest__Enum::B));
        self::assertTrue($instances->has('c', InstancesTest__Enum::B));
        self::assertFalse($instances->has('c'));
    }

    public function testGet(): void {
        $container = Mockery::mock(Container::class);
        $instances = new InstancesTest__Instances($container, InstancesOrder::Asc);
        $aInstance = new class() extends stdClass {
            // empty
        };
        $bInstance = new class() extends stdClass {
            // empty
        };

        $container
            ->shouldReceive('make')
            ->with($bInstance::class)
            ->once()
            ->andReturn($bInstance);

        $instances->add($aInstance, ['aa', 'ab']);
        $instances->add($bInstance::class, ['b', InstancesTest__Enum::B]);

        self::assertSame([$aInstance], iterator_to_array($instances->get('aa'), false));
        self::assertSame([$aInstance, $bInstance], iterator_to_array($instances->get('b', 'ab'), false));
        self::assertSame([$bInstance], iterator_to_array($instances->get('b'), false));
        self::assertSame([$bInstance], iterator_to_array($instances->get(InstancesTest__Enum::B), false));
        self::assertSame([], iterator_to_array($instances->get(InstancesTest__Enum::A), false));

        self::assertSame($aInstance, $instances->first('b', 'aa'));
    }

    public function testGetReverse(): void {
        $container = Mockery::mock(Container::class);
        $instances = new InstancesTest__Instances($container, InstancesOrder::Desc);
        $aInstance = new class() extends stdClass {
            // empty
        };
        $bInstance = new class() extends stdClass {
            // empty
        };

        $container
            ->shouldReceive('make')
            ->with($bInstance::class)
            ->once()
            ->andReturn($bInstance);

        $instances->add($aInstance, ['aa', 'ab']);
        $instances->add($bInstance::class, ['b']);

        self::assertSame([$bInstance, $aInstance], iterator_to_array($instances->get('b', 'ab'), false));

        self::assertSame($bInstance, $instances->first('b', 'aa'));
    }

    public function testAdd(): void {
        $container = Mockery::mock(Container::class);
        $instances = new InstancesTest__Instances($container, InstancesOrder::Asc);
        $aInstance = new class() extends stdClass {
            // empty
        };
        $bInstance = new class() extends stdClass {
            // empty
        };
        $cInstance = new class() extends stdClass {
            // empty
        };
        $dInstance = new class() extends stdClass {
            // empty
        };

        self::assertEquals([], $instances->tags());
        self::assertEquals([], $instances->classes());

        $instances->add($aInstance, ['aa', 'ab'], 200);
        $instances->add($aInstance, ['ac'], merge: true);

        self::assertEquals(['aa', 'ab', 'ac'], $instances->tags());
        self::assertEquals([$aInstance::class], $instances->classes());

        $instances->add($bInstance, ['b'], 100);
        $instances->add($bInstance, ['bb', InstancesTest__Enum::B], 100);

        self::assertEquals(['aa', 'ab', 'ac', 'bb', InstancesTest__Enum::B], $instances->tags());
        self::assertEquals([$bInstance::class, $aInstance::class], $instances->classes());

        $instances->add($cInstance, ['c'], PHP_INT_MAX);
        $instances->add($dInstance, ['d']);

        self::assertEquals(
            [
                $bInstance::class,
                $aInstance::class,
                $dInstance::class,
                $cInstance::class,
            ],
            $instances->classes(),
        );
    }

    public function testRemove(): void {
        $container = Mockery::mock(Container::class);
        $instances = new InstancesTest__Instances($container, InstancesOrder::Desc);
        $aInstance = new class() extends stdClass {
            // empty
        };
        $bInstance = new class() extends stdClass {
            // empty
        };

        $instances->add($aInstance, ['aa', 'ab', InstancesTest__Enum::A]);
        $instances->add($bInstance, ['b', InstancesTest__Enum::B]);

        self::assertEquals(['aa', 'ab', 'b', InstancesTest__Enum::A, InstancesTest__Enum::B], $instances->tags());

        $instances->remove($aInstance);

        self::assertEquals(['b', InstancesTest__Enum::B], $instances->tags());
        self::assertEquals([$bInstance::class], $instances->classes());

        $instances->remove($bInstance);

        self::assertEquals([], $instances->tags());
        self::assertEquals([], $instances->classes());
    }

    public function testReset(): void {
        $container = Mockery::mock(Container::class);
        $instances = new InstancesTest__Instances($container, InstancesOrder::Asc);
        $aInstance = new class() {
            // empty
        };
        $bInstance = new class() extends stdClass {
            // empty
        };

        $instances->add($aInstance, ['a']);
        $instances->add($bInstance::class, ['b']);

        $container
            ->shouldReceive('make')
            ->with($bInstance::class)
            ->twice()
            ->andReturn($bInstance);

        self::assertEquals(
            [
                $aInstance,
                $bInstance,
            ],
            iterator_to_array($instances->get(), false),
        );

        $instances->reset();

        self::assertEquals(
            [
                $aInstance,
                $bInstance,
            ],
            iterator_to_array($instances->get(), false),
        );
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 * @extends Instances<object>
 */
class InstancesTest__Instances extends Instances {
    // empty
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
enum InstancesTest__Enum {
    case A;
    case B;
}
