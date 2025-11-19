<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Utils;

use Illuminate\Contracts\Container\Container;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
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
        $container = Mockery::mock(ContainerResolver::class);
        $instances = new InstancesTest__Instances($container, SortOrder::Desc);
        $aInstance = new class() extends stdClass {
            // empty
        };
        $bInstance = new class() extends stdClass {
            // empty
        };

        $instances->add($aInstance, ['aa', 'ab'], 200);
        $instances->add($bInstance, ['b'], 100);

        self::assertEquals(['aa', 'ab', 'b'], $instances->tags());
    }

    public function testClasses(): void {
        $container = Mockery::mock(ContainerResolver::class);
        $instances = new InstancesTest__Instances($container, SortOrder::Asc);
        $aInstance = new class() extends stdClass {
            // empty
        };
        $bInstance = new class() extends stdClass {
            // empty
        };
        $cInstance = new class() extends stdClass {
            // empty
        };

        $instances->add($aInstance, ['aa', 'ab'], 200);
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
        $container = Mockery::mock(ContainerResolver::class);
        $instances = new InstancesTest__Instances($container, SortOrder::Desc);
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
        $container = Mockery::mock(ContainerResolver::class);
        $instances = new InstancesTest__Instances($container, SortOrder::Desc);
        $aInstance = new class() extends stdClass {
            // empty
        };
        $bInstance = new class() extends stdClass {
            // empty
        };

        self::assertFalse($instances->has());

        $instances->add($aInstance, ['aa', 'ab']);
        $instances->add($bInstance, ['b']);

        self::assertTrue($instances->has());

        self::assertTrue($instances->has('aa'));
        self::assertTrue($instances->has('ab'));
        self::assertTrue($instances->has('b'));
        self::assertTrue($instances->has('c', 'aa'));
        self::assertFalse($instances->has('c'));
    }

    public function testGet(): void {
        $container = Mockery::mock(Container::class);
        $resolver  = Mockery::mock(ContainerResolver::class);
        $resolver
            ->shouldReceive('getInstance')
            ->once()
            ->andReturn($container);

        $instances = new InstancesTest__Instances($resolver, SortOrder::Asc);
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

        self::assertSame([$aInstance], iterator_to_array($instances->get('aa'), false));
        self::assertSame([$aInstance, $bInstance], iterator_to_array($instances->get('b', 'ab'), false));
        self::assertSame([$bInstance], iterator_to_array($instances->get('b'), false));
        self::assertSame([$bInstance], iterator_to_array($instances->get('b'), false));

        self::assertSame($aInstance, $instances->first('b', 'aa'));
    }

    public function testGetReverse(): void {
        $container = Mockery::mock(Container::class);
        $resolver  = Mockery::mock(ContainerResolver::class);
        $resolver
            ->shouldReceive('getInstance')
            ->once()
            ->andReturn($container);

        $instances = new InstancesTest__Instances($resolver, SortOrder::Desc);
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

        self::assertSame([$aInstance], iterator_to_array($instances->get('aa'), false));
        self::assertSame([$bInstance, $aInstance], iterator_to_array($instances->get('b', 'ab'), false));
        self::assertSame([$bInstance], iterator_to_array($instances->get('b'), false));
        self::assertSame([$bInstance], iterator_to_array($instances->get('b'), false));

        self::assertSame($bInstance, $instances->first('b', 'aa'));
    }

    public function testGetNotCacheable(): void {
        $container = Mockery::mock(Container::class);
        $resolver  = Mockery::mock(ContainerResolver::class);
        $resolver
            ->shouldReceive('getInstance')
            ->twice()
            ->andReturn($container);

        $instances = new InstancesTest__Instances($resolver, SortOrder::Asc, false);
        $aInstance = new class() extends stdClass {
            // empty
        };
        $bInstance = new class() extends stdClass {
            // empty
        };

        $container
            ->shouldReceive('make')
            ->with($bInstance::class)
            ->twice()
            ->andReturn($bInstance);

        $instances->add($aInstance, ['a']);
        $instances->add($bInstance::class, ['b']);

        self::assertSame([$aInstance], iterator_to_array($instances->get('a'), false));
        self::assertSame([$aInstance], iterator_to_array($instances->get('a'), false));
        self::assertSame([$bInstance], iterator_to_array($instances->get('b'), false));
        self::assertSame([$bInstance], iterator_to_array($instances->get('b'), false));
    }

    public function testAdd(): void {
        $container = Mockery::mock(ContainerResolver::class);
        $instances = new InstancesTest__Instances($container, SortOrder::Asc);
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
        $instances->add($bInstance, ['bb'], 100);

        self::assertEquals(['aa', 'ab', 'ac', 'bb'], $instances->tags());
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
        $container = Mockery::mock(ContainerResolver::class);
        $instances = new InstancesTest__Instances($container, SortOrder::Desc);
        $aInstance = new class() extends stdClass {
            // empty
        };
        $bInstance = new class() extends stdClass {
            // empty
        };

        $instances->add($aInstance, ['aa', 'ab']);
        $instances->add($bInstance, ['b']);

        self::assertEquals(['aa', 'ab', 'b'], $instances->tags());

        $instances->remove($aInstance);

        self::assertEquals(['b'], $instances->tags());

        $instances->remove($bInstance);

        self::assertEquals([], $instances->tags());
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 * @extends Instances<stdClass>
 */
class InstancesTest__Instances extends Instances {
    // empty
}
