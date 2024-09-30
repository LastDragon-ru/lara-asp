<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Mockery;

use BadMethodCallException;
use LogicException;
use Mockery;
use Mockery\ExpectationDirector;
use Mockery\Mock;
use Mockery\MockInterface;
use Mockery\ReceivedMethodCalls;
use Override;
use ReflectionClass;
use ReflectionProperty;

use function count;

/**
 * Adds support to mocking object properties.
 *
 * Limitations/Notes:
 * * Readonly properties should be uninitialized.
 * * Private properties aren't supported.
 * * Property value must be an object.
 * * Property must be used while test.
 * * Property can be mocked only once.
 * * Objects without methods will be marked as unused.
 *
 * @see https://github.com/mockery/mockery/issues/1142
 *
 * @experimental
 *
 * @phpstan-require-extends Mock
 */
trait MockProperties {
    public function shouldUseProperty(string $name): MockedProperty {
        // Required to avoid "Error: Cannot initialize readonly property X::$name from scope Mockery_*"
        $class = (new ReflectionProperty($this, $name))->getDeclaringClass();

        return new MockedProperty(function (object $value) use ($class, $name): void {
            // Defined?
            $method   = "\${$name}";
            $property = "{$this->mockery_getName()}::\${$name}";
            $director = $this->mockery_getExpectationsFor($method);

            if ($director !== null) {
                throw new BadMethodCallException(
                    "The property `{$property}` already mocked.",
                );
            }

            // Wrap to be able to check usage
            if (!($value instanceof MockInterface)) {
                $value = Mockery::mock($value);
            }

            // Set value
            $class->getProperty($name)->setValue($this, $value);

            // Expectation
            // * required to detect unused properties
            // * todo(testing): is there a better way for this?
            $director = new class ($property, $value) extends ExpectationDirector {
                #[Override]
                public function verify(): void {
                    $count = 0;
                    $calls = (new ReflectionClass($this->_mock))
                        ->getProperty('_mockery_receivedMethodCalls')
                        ->getValue($this->_mock);

                    if ($calls instanceof ReceivedMethodCalls) {
                        $property = (new ReflectionClass($calls))->getProperty('methodCalls');
                        $count    = count((array) $property->getValue($calls));
                    }

                    if ($count === 0) {
                        throw new LogicException("Mocked property `{$this->_name}` is not used.");
                    }
                }
            };

            $this->mockery_setExpectationsFor($method, $director);
        });
    }
}
