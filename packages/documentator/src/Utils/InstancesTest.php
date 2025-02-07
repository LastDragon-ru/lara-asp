<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Utils;

use Illuminate\Contracts\Container\Container;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Instances::class)]
final class InstancesTest extends TestCase {
    public function testIsEmpty(): void {
        $container = Mockery::mock(ContainerResolver::class);
        $instances = new InstancesTest__Instances($container);
        $aInstance = new class() implements InstancesTest__Instance {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function getKeys(): array {
                return ['a'];
            }
        };

        self::assertTrue($instances->isEmpty());

        $instances->add($aInstance);

        self::assertFalse($instances->isEmpty());
    }

    public function testGetKeys(): void {
        $container = Mockery::mock(ContainerResolver::class);
        $instances = new InstancesTest__Instances($container);
        $aInstance = new class() implements InstancesTest__Instance {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function getKeys(): array {
                return ['aa', 'ab'];
            }
        };
        $bInstance = new class() implements InstancesTest__Instance {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function getKeys(): array {
                return ['b'];
            }
        };

        $instances->add($aInstance, 200);
        $instances->add($bInstance, 100);

        self::assertEquals(['aa', 'ab', 'b'], $instances->getKeys());
    }

    public function testGetClassesAndInstances(): void {
        $container = Mockery::mock(ContainerResolver::class);
        $instances = new InstancesTest__Instances($container);
        $aInstance = new class() implements InstancesTest__Instance {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function getKeys(): array {
                return ['aa', 'ab'];
            }
        };
        $bInstance = new class() implements InstancesTest__Instance {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function getKeys(): array {
                return ['b'];
            }
        };
        $cInstance = new class() implements InstancesTest__Instance {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function getKeys(): array {
                return ['c'];
            }
        };

        $instances->add($aInstance, 200);
        $instances->add($bInstance, 100);
        $instances->add($cInstance);

        self::assertEquals(
            [
                $bInstance::class,
                $aInstance::class,
                $cInstance::class,
            ],
            $instances->getClasses(),
        );

        self::assertSame(
            [
                $bInstance,
                $aInstance,
                $cInstance,
            ],
            $instances->getInstances(),
        );
    }

    public function testHas(): void {
        $container = Mockery::mock(ContainerResolver::class);
        $instances = new InstancesTest__Instances($container);
        $aInstance = new class() implements InstancesTest__Instance {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function getKeys(): array {
                return ['aa', 'ab'];
            }
        };
        $bInstance = new class() implements InstancesTest__Instance {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function getKeys(): array {
                return ['b'];
            }
        };

        $instances->add($aInstance);
        $instances->add($bInstance);

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

        $instances = new InstancesTest__Instances($resolver);
        $aInstance = new class() implements InstancesTest__Instance {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function getKeys(): array {
                return ['aa', 'ab'];
            }
        };
        $bInstance = new class() implements InstancesTest__Instance {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function getKeys(): array {
                return ['b'];
            }
        };

        $container
            ->shouldReceive('make')
            ->with($bInstance::class)
            ->once()
            ->andReturn($bInstance);

        $instances->add($aInstance);
        $instances->add($bInstance::class);

        self::assertSame([$aInstance], $instances->get('aa'));
        self::assertSame([$aInstance, $bInstance], $instances->get('b', 'ab'));
        self::assertSame([$bInstance], $instances->get('b'));
        self::assertSame([$bInstance], $instances->get('b'));

        self::assertSame($aInstance, $instances->first('b', 'aa'));
        self::assertSame($bInstance, $instances->last('b', 'aa'));
    }

    public function testGetReverse(): void {
        $container = Mockery::mock(Container::class);
        $resolver  = Mockery::mock(ContainerResolver::class);
        $resolver
            ->shouldReceive('getInstance')
            ->once()
            ->andReturn($container);

        $instances = new InstancesTest__Instances($resolver, true);
        $aInstance = new class() implements InstancesTest__Instance {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function getKeys(): array {
                return ['aa', 'ab'];
            }
        };
        $bInstance = new class() implements InstancesTest__Instance {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function getKeys(): array {
                return ['b'];
            }
        };

        $container
            ->shouldReceive('make')
            ->with($bInstance::class)
            ->once()
            ->andReturn($bInstance);

        $instances->add($aInstance);
        $instances->add($bInstance::class);

        self::assertSame([$aInstance], $instances->get('aa'));
        self::assertSame([$bInstance, $aInstance], $instances->get('b', 'ab'));
        self::assertSame([$bInstance], $instances->get('b'));
        self::assertSame([$bInstance], $instances->get('b'));

        self::assertSame($bInstance, $instances->first('b', 'aa'));
        self::assertSame($aInstance, $instances->last('b', 'aa'));
    }

    public function testAdd(): void {
        $container = Mockery::mock(ContainerResolver::class);
        $instances = new InstancesTest__Instances($container);
        $aInstance = new class() implements InstancesTest__Instance {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function getKeys(): array {
                return ['aa', 'ab'];
            }
        };
        $bInstance = new class() implements InstancesTest__Instance {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function getKeys(): array {
                return ['b'];
            }
        };

        self::assertEquals([], $instances->getKeys());
        self::assertEquals([], $instances->getClasses());

        $instances->add($aInstance, 200);

        self::assertEquals(['aa', 'ab'], $instances->getKeys());
        self::assertEquals([$aInstance::class], $instances->getClasses());

        $instances->add($bInstance, 100);

        self::assertEquals(['aa', 'ab', 'b'], $instances->getKeys());
        self::assertEquals([$bInstance::class, $aInstance::class], $instances->getClasses());
    }

    public function testRemove(): void {
        $container = Mockery::mock(ContainerResolver::class);
        $instances = new InstancesTest__Instances($container);
        $aInstance = new class() implements InstancesTest__Instance {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function getKeys(): array {
                return ['aa', 'ab'];
            }
        };
        $bInstance = new class() implements InstancesTest__Instance {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function getKeys(): array {
                return ['b'];
            }
        };

        $instances->add($aInstance);
        $instances->add($bInstance);

        self::assertEquals(['aa', 'ab', 'b'], $instances->getKeys());

        $instances->remove($aInstance);

        self::assertEquals(['b'], $instances->getKeys());

        $instances->remove($bInstance);

        self::assertEquals([], $instances->getKeys());
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 * @extends Instances<InstancesTest__Instance>
 */
class InstancesTest__Instances extends Instances {
    public function __construct(
        ContainerResolver $container,
        private readonly bool $reverse = false,
    ) {
        parent::__construct($container);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    protected function getInstanceKeys(object|string $instance): array {
        return $instance::getKeys();
    }

    #[Override]
    protected function isHighPriorityFirst(): bool {
        return $this->reverse;
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
interface InstancesTest__Instance {
    /**
     * @return list<string>
     */
    public static function getKeys(): array;
}
