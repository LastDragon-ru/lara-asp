<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer;

use DateTimeInterface;
use Exception;
use Illuminate\Support\Carbon;
use JsonSerializable;
use LastDragon_ru\LaraASP\Serializer\Contracts\Partial;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer as SerializerContract;
use LastDragon_ru\LaraASP\Serializer\Exceptions\FailedToDeserialize;
use LastDragon_ru\LaraASP\Serializer\Exceptions\FailedToSerialize;
use LastDragon_ru\LaraASP\Serializer\Exceptions\PartialUnserializable;
use LastDragon_ru\LaraASP\Serializer\Normalizers\SerializableNormalizer;
use LastDragon_ru\LaraASP\Serializer\Testing\Package\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Stringable;
use Symfony\Component\Serializer\Annotation\DiscriminatorMap;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;
use Symfony\Component\Serializer\Exception\MissingConstructorArgumentsException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

use function get_debug_type;
use function is_string;
use function sprintf;

/**
 * @internal
 */
#[CoversClass(Provider::class)]
#[CoversClass(Serializer::class)]
#[CoversClass(SerializableNormalizer::class)]
final class ProviderTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    public function testRegister(): void {
        self::assertSame(
            $this->app()->make(SerializerContract::class),
            $this->app()->make(SerializerContract::class),
        );
    }

    #[DataProvider('dataProviderSerialization')]
    public function testSerialization(Exception|string $expected, Serializable $serializable): void {
        try {
            $serializer = $this->app()->make(SerializerContract::class);
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
     * @param class-string<Serializable> $class
     */
    #[DataProvider('dataProviderDeserialization')]
    public function testDeserialization(Exception|Serializable $expected, string $class, string $serialized): void {
        try {
            $serializer   = $this->app()->make(SerializerContract::class);
            $deserialized = $serializer->deserialize($class, $serialized);

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
        $unitEnum     = ProviderTest__UnitEnum::A;
        $backedEnum   = ProviderTest__BackedEnum::A;
        $serializable = new ProviderTest__Simple($datetime, $unitEnum, $backedEnum);
        $partial      = new ProviderTest__Partial(1, 2);
        $curcular     = new class() implements Serializable {
            public Serializable $a; // @phpstan-ignore-line property.uninitialized (required for tests)
        };
        $curcular->a  = $curcular;

        return [
            'empty object'                    => [
                '{}',
                new ProviderTest__Empty(),
            ],
            'simple object'                   => [
                <<<'JSON'
                {
                    "a": 123,
                    "e": "2023-08-11T10:45:00.000+00:00",
                    "f": "A",
                    "g": 0,
                    "hRenamed": "renamed"
                }
                JSON,
                $serializable,
            ],
            'complex object'                  => [
                <<<'JSON'
                {
                    "a": 123,
                    "flags": [1,2,3],
                    "datetime": "2023-08-11T10:45:00.000+00:00",
                    "nested": {
                        "a": 123,
                        "e": "2023-08-11T10:45:00.000+00:00",
                        "f": "A",
                        "g": 0,
                        "hRenamed": "renamed"
                    },
                    "array": ["2023-08-11T10:45:00.000+00:00","2023-08-11T10:45:00.000+00:00"],
                    "nullable": null,
                    "unitEnum":"A",
                    "backedEnum": 0
                }
                JSON,
                new ProviderTest__Complex(
                    $datetime,
                    $serializable,
                    [$datetime, $datetime],
                    null,
                    $unitEnum,
                    $backedEnum,
                ),
            ],
            'unsupported object'              => [
                new FailedToSerialize($invalid, 'json', [], new NotNormalizableValueException(
                    sprintf(
                        'Could not normalize object of type "%s", no supporting normalizer found.',
                        get_debug_type($object),
                    ),
                )),
                $invalid,
            ],
            'circular reference'              => [
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
            'abstract: without discriminator' => [
                <<<'JSON'
                {
                    "discriminator": "a",
                    "property": "a"
                }
                JSON,
                new ProviderTest__A('a'),
            ],
            'abstract: with discriminator'    => [
                <<<'JSON'
                {
                    "discriminator": "c",
                    "property": "c"
                }
                JSON,
                new ProviderTest__C('invalid', 'c'),
            ],
            Partial::class                    => [
                new FailedToSerialize($partial, 'json', [], new PartialUnserializable()),
                $partial,
            ],
        ];
    }

    /**
     * @return array<string, array{Exception|Serializable, class-string<Serializable>, string}>
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
        $unitEnum     = ProviderTest__UnitEnum::B;
        $backedEnum   = ProviderTest__BackedEnum::B;
        $serializable = new ProviderTest__Simple($datetime, $unitEnum, $backedEnum);

        return [
            'empty object'                       => [
                new ProviderTest__Empty(),
                ProviderTest__Empty::class,
                '{}',
            ],
            'simple object'                      => [
                $serializable,
                $serializable::class,
                <<<'JSON'
                {
                    "a": 123,
                    "e": "2023-08-11T10:45:00.000+00:00",
                    "f": "B",
                    "g": 1,
                    "hRenamed": "renamed"
                }
                JSON,
            ],
            'complex object'                     => [
                new ProviderTest__Complex(
                    $datetime,
                    new ProviderTest__Simple(),
                    [$datetime, $datetime],
                    null,
                    $unitEnum,
                    $backedEnum,
                ),
                ProviderTest__Complex::class,
                <<<'JSON'
                {
                    "a": 123,
                    "datetime": "2023-08-11T10:45:00.000+00:00",
                    "nested": {"a":123},
                    "flags": [1, 2, 3],
                    "array": ["2023-08-11T10:45:00.000+00:00","2023-08-11T10:45:00.000+00:00"],
                    "nullable": null,
                    "unitEnum": "B",
                    "backedEnum": 1
                }
                JSON,
            ],
            'unsupported object'                 => [
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
            'unknown property'                   => [
                new FailedToDeserialize(ProviderTest__Simple::class, '', 'json', [], new ExtraAttributesException([
                    'unknown',
                ])),
                ProviderTest__Simple::class,
                '{"unknown": 123}',
            ],
            'incomplete object with constructor' => [
                new FailedToDeserialize(
                    ProviderTest__Complex::class,
                    '',
                    'json',
                    [],
                    new MissingConstructorArgumentsException(
                        sprintf(
                            'Cannot create an instance of "%s" from serialized'
                            .' data because its constructor requires the following'
                            .' parameters to be present : "$%s".',
                            ProviderTest__Complex::class,
                            'array',
                        ),
                    ),
                ),
                ProviderTest__Complex::class,
                <<<'JSON'
                {
                    "datetime": "2023-08-11T10:45:00.000+00:00",
                    "nested": {"a":123}
                }
                JSON,
            ],
            'abstract: without discriminator'    => [
                new ProviderTest__B('a', 'b'),
                ProviderTest__Abstract::class,
                <<<'JSON'
                {
                    "discriminator": "b",
                    "property": "a",
                    "another": "b"
                }
                JSON,
            ],
            'abstract: with discriminator'       => [
                new ProviderTest__C('c', 'c'),
                ProviderTest__Abstract::class,
                <<<'JSON'
                {
                    "discriminator": "c",
                    "property": "c"
                }
                JSON,
            ],
            'abstract: missed discriminator'     => [
                new FailedToDeserialize(
                    ProviderTest__Abstract::class,
                    '',
                    'json',
                    [],
                    new NotNormalizableValueException(
                        sprintf(
                            'Type property "%s" not found for the abstract object "%s".',
                            'discriminator',
                            ProviderTest__Abstract::class,
                        ),
                    ),
                ),
                ProviderTest__Abstract::class,
                <<<'JSON'
                {
                    "property": "c"
                }
                JSON,
            ],
            Partial::class                       => [
                new ProviderTest__Partial(1, 2),
                ProviderTest__Partial::class,
                <<<'JSON'
                {
                    "a": 1,
                    "b": 2,
                    "c": 3
                }
                JSON,
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
    public int                       $a = 123;
    public bool                      $b; // @phpstan-ignore-line property.uninitialized (required for tests)
    protected string                 $c = 'should be ignored';
    private string                   $d = 'should be ignored';
    public ?DateTimeInterface        $e = null;
    public ?ProviderTest__UnitEnum   $f = null;
    public ?ProviderTest__BackedEnum $g;

    #[SerializedName('hRenamed')]
    public string $h = 'renamed';

    public function __construct(
        ?DateTimeInterface $e = null,
        ?ProviderTest__UnitEnum $f = null,
        ?ProviderTest__BackedEnum $g = null,
    ) {
        $this->e = $e;
        $this->f = $f;
        $this->g = $g;
    }

    #[Override]
    public function __toString(): string {
        return $this->d;
    }

    #[Override]
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
    public bool $b; // @phpstan-ignore-line property.uninitialized (required for tests)

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
        public ?ProviderTest__UnitEnum $unitEnum,
        public ?ProviderTest__BackedEnum $backedEnum,
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

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
#[DiscriminatorMap('discriminator', [
    'a' => ProviderTest__A::class,
    'b' => ProviderTest__B::class,
    'c' => ProviderTest__C::class,
])]
class ProviderTest__Abstract implements Serializable {
    public function __construct(
        public string $property,
    ) {
        // empty
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ProviderTest__A extends ProviderTest__Abstract {
    // empty
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ProviderTest__B extends ProviderTest__Abstract {
    public function __construct(
        string $property,
        public string $another,
    ) {
        parent::__construct($property);
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ProviderTest__C extends ProviderTest__Abstract {
    public function __construct(
        public string $discriminator,
        string $property,
    ) {
        parent::__construct($property);
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
enum ProviderTest__UnitEnum {
    case A;
    case B;
    case C;
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
enum ProviderTest__BackedEnum: int {
    case A = 0;
    case B = 1;
    case C = 2;
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ProviderTest__Partial implements Serializable, Partial {
    public function __construct(
        public readonly int $a,
        public readonly int $b,
    ) {
        // empty
    }
}
