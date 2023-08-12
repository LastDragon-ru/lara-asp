<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer;

use Exception;
use Illuminate\Container\Container;
use Illuminate\Support\Carbon;
use JsonSerializable;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;
use LastDragon_ru\LaraASP\Serializer\Exceptions\FailedToDeserialize;
use LastDragon_ru\LaraASP\Serializer\Exceptions\FailedToSerialize;
use LastDragon_ru\LaraASP\Serializer\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Stringable;
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

use function get_debug_type;
use function is_string;
use function sprintf;

/**
 * @internal
 */
#[CoversClass(Provider::class)]
class ProviderTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    public function testRegister(): void {
        self::assertSame(
            Container::getInstance()->make(Serializer::class),
            Container::getInstance()->make(Serializer::class),
        );
    }

    /**
     * @dataProvider dataProviderSerialization
     */
    public function testSerialization(Exception|string $expected, Serializable $serializable): void {
        try {
            $serializer = Container::getInstance()->make(Serializer::class);
            $serialized = $serializer->serialize($serializable);

            if (is_string($expected)) {
                self::assertJsonStringEqualsJsonString($expected, $serialized);
            } else {
                self::fail('Something wrong...');
            }
        } catch (Exception $exception) {
            if ($expected instanceof Exception) {
                self::assertEquals(
                    $this->getExceptionMessages($expected),
                    $this->getExceptionMessages($exception),
                );
            } else {
                throw $exception;
            }
        }
    }

    /**
     * @dataProvider dataProviderDeserialization
     *
     * @param class-string<Serializable>|null $class
     */
    public function testDeserialization(Exception|Serializable $expected, ?string $class, string $serialized): void {
        try {
            $serializer   = Container::getInstance()->make(Serializer::class);
            $deserialized = $serializer->deserialize($class ?? $expected::class, $serialized);

            if ($expected instanceof Serializable) {
                self::assertEquals($expected, $deserialized);
            } else {
                self::fail('Something wrong...');
            }
        } catch (Exception $exception) {
            if ($expected instanceof Exception) {
                self::assertEquals(
                    $this->getExceptionMessages($expected),
                    $this->getExceptionMessages($exception),
                );
            } else {
                throw $exception;
            }
        }
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    /**
     * @return list<string>
     */
    private function getExceptionMessages(Exception $exception): array {
        $messages = [];

        do {
            $messages[] = $exception->getMessage();
            $exception  = $exception->getPrevious();
        } while ($exception);

        return $messages;
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{Exception|string, Serializable}>
     */
    public static function dataProviderSerialization(): array {
        $object       = new ProviderTest__Class();
        $invalid      = new class($object) implements Serializable {
            public function __construct(
                public ProviderTest__Class $a,
            ) {
                // empty
            }
        };
        $datetime     = Carbon::make('2023-08-11T10:45:00');
        $serializable = new ProviderTest__Simple();
        $curcular     = new class() implements Serializable {
            public Serializable $a; // @phpstan-ignore-line required for tests
        };
        $curcular->a  = $curcular;

        return [
            'empty object'       => [
                '{}',
                new ProviderTest__Empty(),
            ],
            'simple object'      => [
                '{"a": 123}',
                $serializable,
            ],
            'complex object'     => [
                <<<'JSON'
                {
                    "a": 123,
                    "flags": [1,2,3],
                    "datetime": "2023-08-11T10:45:00.000+00:00",
                    "nested": {"a":123},
                    "array": ["2023-08-11T10:45:00.000+00:00","2023-08-11T10:45:00.000+00:00"],
                    "nullable": null
                }
                JSON,
                new ProviderTest__Complex($datetime, $serializable, [$datetime, $datetime], null),
            ],
            'unsupported object' => [
                new FailedToSerialize($invalid, 'json', [], new NotNormalizableValueException(
                    sprintf(
                        'Could not normalize object of type "%s", no supporting normalizer found.',
                        get_debug_type($object),
                    ),
                )),
                $invalid,
            ],
            'circular reference' => [
                new FailedToSerialize($curcular, 'json', [], new CircularReferenceException(
                    sprintf(
                        'A circular reference has been detected when serializing the'
                        .' object of class "%s" (configured limit: %d).',
                        get_debug_type($curcular),
                        1,
                    ),
                )),
                $curcular,
            ],
        ];
    }

    /**
     * @return array<string, array{Exception|Serializable, class-string<Serializable>|null, string}>
     */
    public static function dataProviderDeserialization(): array {
        $object       = new ProviderTest__Class();
        $invalid      = new class($object) implements Serializable {
            public function __construct(
                public ProviderTest__Class $a,
            ) {
                // empty
            }
        };
        $datetime     = Carbon::make('2023-08-11T10:45:00');
        $serializable = new ProviderTest__Simple();

        return [
            'empty object'       => [
                new ProviderTest__Empty(),
                null,
                '{}',
            ],
            'simple object'      => [
                $serializable,
                null,
                '{"a": 123}',
            ],
            'complex object'     => [
                new ProviderTest__Complex($datetime, $serializable, [$datetime, $datetime], null),
                null,
                <<<'JSON'
                {
                    "a": 123,
                    "datetime": "2023-08-11T10:45:00.000+00:00",
                    "nested": {"a":123},
                    "flags": [1, 2, 3],
                    "array": ["2023-08-11T10:45:00.000+00:00","2023-08-11T10:45:00.000+00:00"],
                    "nullable": null
                }
                JSON,
            ],
            'unsupported object' => [
                new FailedToDeserialize($invalid::class, '', 'json', [], new NotNormalizableValueException(
                    sprintf(
                        'The type of the "%s" attribute for class "%s" must be one of "%s" ("%s" given).',
                        'a',
                        $invalid::class,
                        ProviderTest__Class::class,
                        'array',
                    ),
                )),
                $invalid::class,
                '{"a": {}}',
            ],
            'unknown property'   => [
                new FailedToDeserialize(ProviderTest__Simple::class, '', 'json', [], new ExtraAttributesException([
                    'unknown',
                ])),
                ProviderTest__Simple::class,
                '{"unknown": 123}',
            ],
        ];
    }
    // </editor-fold>
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ProviderTest__Simple implements Serializable, Stringable, JsonSerializable {
    public int       $a = 123;
    public bool      $b; // @phpstan-ignore-line required for tests
    protected string $c = 'should be ignored';
    private string   $d = 'should be ignored';

    public function __toString(): string {
        return $this->d;
    }

    public function jsonSerialize(): mixed {
        return [
            'c' => $this->c,
            'd' => $this->d,
        ];
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ProviderTest__Complex implements Serializable {
    public int  $a = 123;
    public bool $b; // @phpstan-ignore-line required for tests

    /**
     * @var array<int, int>
     */
    public array $flags = [1, 2, 3];

    /**
     * @param array<array-key, ?Carbon> $array
     */
    public function __construct(
        public ?Carbon $datetime,
        public ProviderTest__Simple $nested,
        public array $array,
        public ?Carbon $nullable,
    ) {
        // empty
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ProviderTest__Empty implements Serializable {
    // empty
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ProviderTest__Class {
    // empty
}
