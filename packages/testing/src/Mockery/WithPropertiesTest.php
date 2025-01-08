<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Mockery;

use BadMethodCallException;
use LastDragon_ru\LaraASP\Testing\Testing\TestCase;
use LogicException;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionException;

use function sprintf;

/**
 * @internal
 */
#[CoversClass(WithProperties::class)]
final class WithPropertiesTest extends TestCase {
    public function testShouldUsePropertyValueIsObject(): void {
        $mock = Mockery::mock(WithPropertiesTest_ObjectProtected::class, new WithProperties(), PropertiesMock::class);
        $mock->makePartial();
        $mock
            ->shouldUseProperty('value')
            ->value(
                new WithPropertiesTest_Value('abc'),
            );

        self::assertEquals('abc', $mock->getValue());
    }

    public function testShouldUsePropertyValueIsMock(): void {
        $mock = Mockery::mock(WithPropertiesTest_ObjectProtected::class, new WithProperties(), PropertiesMock::class);
        $mock->makePartial();
        $mock
            ->shouldUseProperty('value')
            ->value(
                Mockery::mock(WithPropertiesTest_Value::class)
                    ->shouldReceive('getValue')
                    ->once()
                    ->andReturn('abc')
                    ->getMock(),
            );

        self::assertEquals('abc', $mock->getValue());
    }

    public function testShouldUsePropertyUnused(): void {
        $mock = Mockery::mock(WithPropertiesTest_ObjectProtected::class, new WithProperties(), PropertiesMock::class);
        $mock->makePartial();
        $mock
            ->shouldUseProperty('value')
            ->value(
                new WithPropertiesTest_Value('abc'),
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
        $mock = Mockery::mock(WithPropertiesTest_ObjectProtected::class, new WithProperties(), PropertiesMock::class);
        $mock->makePartial();
        $mock
            ->shouldUseProperty('value')
            ->value(
                new WithPropertiesTest_Value('abc'),
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
                new WithPropertiesTest_Value('abc'),
            );
    }

    public function testShouldUsePublicProperty(): void {
        $mock = Mockery::mock(WithPropertiesTest_ObjectPublic::class, new WithProperties(), PropertiesMock::class);
        $mock->makePartial();
        $mock
            ->shouldUseProperty('value')
            ->value(
                new WithPropertiesTest_Value('public'),
            );

        self::assertEquals('public', $mock->getValue());
    }

    public function testShouldUseProtectedProperty(): void {
        $mock = Mockery::mock(WithPropertiesTest_ObjectProtected::class, new WithProperties(), PropertiesMock::class);
        $mock->makePartial();
        $mock
            ->shouldUseProperty('value')
            ->value(
                new WithPropertiesTest_Value('protected'),
            );

        self::assertEquals('protected', $mock->getValue());
    }

    public function testShouldUsePrivateProperty(): void {
        $mock = Mockery::mock(WithPropertiesTest_ObjectPrivate::class, new WithProperties(), PropertiesMock::class);
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
                new WithPropertiesTest_Value('private'),
            );
    }

    public function testShouldUseReadonlyProperty(): void {
        $mock = Mockery::mock(WithPropertiesTest_ObjectReadonly::class, new WithProperties(), PropertiesMock::class);
        $mock->makePartial();
        $mock
            ->shouldUseProperty('value')
            ->value(
                new WithPropertiesTest_Value('readonly'),
            );

        self::assertEquals('readonly', $mock->getValue());
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class WithPropertiesTest_ObjectPrivate {
    public function __construct(
        private WithPropertiesTest_Value $value,
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
class WithPropertiesTest_ObjectProtected {
    public function __construct(
        protected WithPropertiesTest_Value $value,
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
class WithPropertiesTest_ObjectPublic extends WithPropertiesTest_ObjectProtected {
    public function __construct(
        public WithPropertiesTest_Value $value,
    ) {
        parent::__construct($this->value);
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class WithPropertiesTest_ObjectReadonly {
    public function __construct(
        protected readonly WithPropertiesTest_Value $value,
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
class WithPropertiesTest_Value {
    public function __construct(
        protected string $value,
    ) {
        // empty
    }

    public function getValue(): string {
        return $this->value;
    }
}
