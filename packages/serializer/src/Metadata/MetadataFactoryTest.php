<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer\Metadata;

use JsonSerializable;
use LastDragon_ru\LaraASP\Serializer\Attributes\VersionMap;
use LastDragon_ru\LaraASP\Serializer\Testing\Package\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Serializer\Attribute\DiscriminatorMap;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Serializer\Mapping\AttributeMetadata;

use function tap;

/**
 * @internal
 */
#[CoversClass(MetadataFactory::class)]
final class MetadataFactoryTest extends TestCase {
    public function testHasMetadataFor(): void {
        $factory = new MetadataFactory();

        self::assertTrue(
            $factory->hasMetadataFor(MetadataFactoryTest_A::class), // @phpstan-ignore-line method.alreadyNarrowedType
        );
        self::assertTrue(
            $factory->hasMetadataFor(new MetadataFactoryTest_A()),  // @phpstan-ignore-line method.alreadyNarrowedType
        );
        self::assertFalse(
            $factory->hasMetadataFor(JsonSerializable::class),      // @phpstan-ignore-line method.alreadyNarrowedType
        );
        self::assertFalse(
            $factory->hasMetadataFor('UnknownClass'),               // @phpstan-ignore-line method.impossibleType
        );
    }

    public function testGetMetadataFor(): void {
        $factory = new MetadataFactory();
        $a       = $factory->getMetadataFor(MetadataFactoryTest_A::class);
        $b       = $factory->getMetadataFor(MetadataFactoryTest_B::class);
        $c       = $factory->getMetadataFor(MetadataFactoryTest_C::class);

        self::assertEquals(
            [
                'a'        => new AttributeMetadata('a'),
                'b'        => new AttributeMetadata('b'),
                'f'        => tap(
                    new AttributeMetadata('f'),
                    static function (AttributeMetadata $metadata): void {
                        $metadata->setSerializedName('ff');
                    },
                ),
                'array'    => new AttributeMetadata('array'),
                'promoted' => new AttributeMetadata('promoted'),
                'renamed'  => tap(
                    new AttributeMetadata('renamed'),
                    static function (AttributeMetadata $metadata): void {
                        $metadata->setSerializedName('promoted-renamed');
                    },
                ),
            ],
            $b->getAttributesMetadata(),
        );
        self::assertEquals(
            [
                'property' => 'version',
                'mapping'  => [
                    'a' => MetadataFactoryTest_A::class,
                    'b' => MetadataFactoryTest_B::class,
                ],
            ],
            [
                'property' => $a->getClassDiscriminatorMapping()?->getTypeProperty(),
                'mapping'  => $a->getClassDiscriminatorMapping()?->getTypesMapping(),
            ],
        );
        self::assertEquals(
            [
                'property' => null,
                'mapping'  => null,
            ],
            [
                'property' => $b->getClassDiscriminatorMapping()?->getTypeProperty(),
                'mapping'  => $b->getClassDiscriminatorMapping()?->getTypesMapping(),
            ],
        );
        self::assertEquals(
            [
                'property' => '$v',
                'mapping'  => [
                    'a' => MetadataFactoryTest_A::class,
                    'b' => MetadataFactoryTest_B::class,
                ],
            ],
            [
                'property' => $c->getClassDiscriminatorMapping()?->getTypeProperty(),
                'mapping'  => $c->getClassDiscriminatorMapping()?->getTypesMapping(),
            ],
        );
    }

    public function testGetTypes(): void {
        $factory = new MetadataFactory();
        $class   = MetadataFactoryTest_B::class;

        self::assertEquals(
            [
                new Type('int'),
            ],
            $factory->getTypes($class, 'a'),
        );
        self::assertNull(
            $factory->getTypes($class, 'c'),
        );
        self::assertNull(
            $factory->getTypes($class, 'd'),
        );
        self::assertEquals(
            [
                new Type(
                    builtinType        : 'array',
                    collection         : true,
                    collectionKeyType  : [
                        new Type('string'),
                        new Type('int'),
                    ],
                    collectionValueType: [
                        new Type(
                            builtinType: 'object',
                            class      : MetadataFactoryTest_A::class,
                        ),
                    ],
                ),
            ],
            $factory->getTypes($class, 'array'),
        );
        self::assertEquals(
            [
                new Type(
                    builtinType        : 'array',
                    collection         : true,
                    collectionKeyType  : [
                        new Type('int'),
                    ],
                    collectionValueType: [
                        new Type(
                            builtinType: 'object',
                            class      : MetadataFactoryTest_B::class,
                        ),
                    ],
                ),
            ],
            $factory->getTypes($class, 'promoted'),
        );
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
#[DiscriminatorMap('version', ['b' => MetadataFactoryTest_B::class, 'a' => MetadataFactoryTest_A::class])]
class MetadataFactoryTest_A implements JsonSerializable {
    public int           $a = 123;
    public bool          $b; // @phpstan-ignore-line property.uninitialized (required for tests)
    protected string     $c = 'should be ignored';
    private string       $d = 'should be ignored';
    public static string $e = 'should be ignored';
    #[SerializedName('ff')]
    public string        $f = 'string';

    #[Override]
    public function jsonSerialize(): mixed {
        return ['d' => $this->d];
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class MetadataFactoryTest_B extends MetadataFactoryTest_A {
    /**
     * @var array<array-key, MetadataFactoryTest_A>
     * @phpstan-ignore-next-line property.uninitialized (required for tests)
     */
    public array $array;

    /**
     * @param array<int, MetadataFactoryTest_B> $promoted
     */
    public function __construct(
        public array $promoted = [],
        #[SerializedName('promoted-renamed')]
        public string $renamed = 'string',
    ) {
        // empty
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
#[VersionMap(['b' => MetadataFactoryTest_B::class, 'a' => MetadataFactoryTest_A::class])]
class MetadataFactoryTest_C {
    // empty
}
