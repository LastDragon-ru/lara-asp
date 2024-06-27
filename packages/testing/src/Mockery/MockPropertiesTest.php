<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Mockery;

use BadMethodCallException;
use LastDragon_ru\LaraASP\Testing\Testing\TestCase;
use LogicException;
use Mockery;
use Mockery\Mock;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionException;

use function sprintf;

/**
 * @internal
 */
#[CoversClass(MockProperties::class)]
final class MockPropertiesTest extends TestCase {
    public function testShouldUsePropertyValueIsObject(): void {
        $mock = Mockery::mock(MockPropertiesTest_ObjectProtected::class, MockProperties::class);
        $mock->makePartial();
        $mock
            ->shouldUseProperty('value')
            ->value(
                new MockPropertiesTest_Value('abc'),
            );

        self::assertEquals('abc', $mock->getValue());
    }

    public function testShouldUsePropertyValueIsMock(): void {
        $mock = Mockery::mock(MockPropertiesTest_ObjectProtected::class, MockProperties::class);
        $mock->makePartial();
        $mock
            ->shouldUseProperty('value')
            ->value(
                Mockery::mock(MockPropertiesTest_Value::class)
                    ->shouldReceive('getValue')
                    ->once()
                    ->andReturn('abc')
                    ->getMock(),
            );

        self::assertEquals('abc', $mock->getValue());
    }

    public function testShouldUsePropertyUnused(): void {
        $mock = Mockery::mock(MockPropertiesTest_ObjectProtected::class, MockProperties::class);
        $mock->makePartial();
        $mock
            ->shouldUseProperty('value')
            ->value(
                new MockPropertiesTest_Value('abc'),
            );

        self::expectException(LogicException::class);
        self::expectExceptionMessage(
            sprintf(
                'Mocked property `%s::$value` is not used.',
                $mock::class,
            ),
        );

        $mock->getDefault();

        Mockery::close();
    }

    public function testShouldUsePropertyRedefineNotAllowed(): void {
        $mock = Mockery::mock(MockPropertiesTest_ObjectProtected::class, MockProperties::class);
        $mock->makePartial();
        $mock
            ->shouldUseProperty('value')
            ->value(
                new MockPropertiesTest_Value('abc'),
            );

        $mock->getValue();

        self::expectException(BadMethodCallException::class);
        self::expectExceptionMessage(
            sprintf(
                'The property `%s::$value` already mocked.',
                $mock::class,
            ),
        );

        $mock
            ->shouldUseProperty('value')
            ->value(
                new MockPropertiesTest_Value('abc'),
            );
    }

    public function testShouldUsePublicProperty(): void {
        $mock = Mockery::mock(MockPropertiesTest_ObjectPublic::class, MockProperties::class);
        $mock->makePartial();
        $mock
            ->shouldUseProperty('value')
            ->value(
                new MockPropertiesTest_Value('public'),
            );

        self::assertEquals('public', $mock->getValue());
    }

    public function testShouldUseProtectedProperty(): void {
        $mock = Mockery::mock(MockPropertiesTest_ObjectProtected::class, MockProperties::class);
        $mock->makePartial();
        $mock
            ->shouldUseProperty('value')
            ->value(
                new MockPropertiesTest_Value('protected'),
            );

        self::assertEquals('protected', $mock->getValue());
    }

    public function testShouldUsePrivateProperty(): void {
        $mock = Mockery::mock(MockPropertiesTest_ObjectPrivate::class, MockProperties::class);
        $mock->makePartial();

        self::expectException(ReflectionException::class);
        self::expectExceptionMessage(
            sprintf(
                '%s::$value does not exist',
                $mock::class,
            ),
        );

        $mock
            ->shouldUseProperty('value')
            ->value(
                new MockPropertiesTest_Value('private'),
            );
    }

    public function testShouldUseReadonlyProperty(): void {
        $mock = Mockery::mock(MockPropertiesTest_ObjectReadonly::class, MockProperties::class);
        $mock->makePartial();
        $mock
            ->shouldUseProperty('value')
            ->value(
                new MockPropertiesTest_Value('readonly'),
            );

        self::assertEquals('readonly', $mock->getValue());
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * Exists only for phpstan because it cannot analyze unused traits.
 *
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class MockPropertiesTest_Mock extends Mock {
    use MockProperties;
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class MockPropertiesTest_ObjectPrivate {
    public function __construct(
        private MockPropertiesTest_Value $value,
    ) {
        // empty
    }

    public function getValue(): string {
        return $this->value->getValue();
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class MockPropertiesTest_ObjectProtected {
    public function __construct(
        protected MockPropertiesTest_Value $value,
    ) {
        // empty
    }

    public function getValue(): string {
        return $this->value->getValue();
    }

    public function getDefault(): string {
        return 'default';
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class MockPropertiesTest_ObjectPublic extends MockPropertiesTest_ObjectProtected {
    public function __construct(
        public MockPropertiesTest_Value $value,
    ) {
        parent::__construct($this->value);
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class MockPropertiesTest_ObjectReadonly {
    public function __construct(
        protected readonly MockPropertiesTest_Value $value,
    ) {
        // empty
    }

    public function getValue(): string {
        return $this->value->getValue();
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class MockPropertiesTest_Value {
    public function __construct(
        protected string $value,
    ) {
        // empty
    }

    public function getValue(): string {
        return $this->value;
    }
}
