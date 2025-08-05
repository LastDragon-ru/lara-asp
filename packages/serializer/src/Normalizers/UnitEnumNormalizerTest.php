<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer\Normalizers;

use LastDragon_ru\LaraASP\Serializer\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;

use function sprintf;

/**
 * @internal
 */
#[CoversClass(UnitEnumNormalizer::class)]
final class UnitEnumNormalizerTest extends TestCase {
    public function testNormalize(): void {
        $normalizer = new UnitEnumNormalizer();
        $enumAA     = UnitEnumNormalizerTest__EnumA::A;
        $enumAB     = UnitEnumNormalizerTest__EnumA::B;
        $enumBB     = UnitEnumNormalizerTest__EnumB::B;
        $enumBC     = UnitEnumNormalizerTest__EnumB::C;

        self::assertSame($enumAA->name, $normalizer->normalize($enumAA));
        self::assertSame($enumAB->name, $normalizer->normalize($enumAB));
        self::assertSame($enumBB->name, $normalizer->normalize($enumAB));
        self::assertSame($enumBB->name, $normalizer->normalize($enumBB));
        self::assertSame($enumBC->name, $normalizer->normalize($enumBC));
    }

    public function testNormalizeNotEnum(): void {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(
            sprintf(
                'The `$object` expected to be a `UnitEnum`, `%s` given.',
                stdClass::class,
            ),
        );

        (new UnitEnumNormalizer())->normalize(new stdClass());
    }

    public function testNormalizeBackedEnum(): void {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(
            sprintf(
                'The `$object` expected to be a `UnitEnum`, `%s` given.',
                UnitEnumNormalizerTest__BackedEnum::class,
            ),
        );

        (new UnitEnumNormalizer())->normalize(UnitEnumNormalizerTest__BackedEnum::A);
    }

    public function testDenormalize(): void {
        $normalizer = new UnitEnumNormalizer();
        $enumAA     = UnitEnumNormalizerTest__EnumA::A;
        $enumAB     = UnitEnumNormalizerTest__EnumA::B;
        $enumBB     = UnitEnumNormalizerTest__EnumB::B;
        $enumBC     = UnitEnumNormalizerTest__EnumB::C;

        self::assertSame($enumAA, $normalizer->denormalize($enumAA->name, $enumAA::class));
        self::assertSame($enumAB, $normalizer->denormalize($enumAB->name, $enumAB::class));
        self::assertSame($enumBB, $normalizer->denormalize($enumAB->name, $enumBB::class));
        self::assertSame($enumBB, $normalizer->denormalize($enumBB->name, $enumBB::class));
        self::assertSame($enumBC, $normalizer->denormalize($enumBC->name, $enumBC::class));
    }

    public function testDenormalizeNotEnum(): void {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(
            sprintf(
                'The `$type` expected to be a `UnitEnum`, `%s` given.',
                stdClass::class,
            ),
        );

        (new UnitEnumNormalizer())->denormalize('value', stdClass::class);
    }

    public function testDenormalizeInvalid(): void {
        self::expectException(UnexpectedValueException::class);
        self::expectExceptionMessage(
            sprintf(
                'The data must belong to a enumeration of type `%s`.',
                UnitEnumNormalizerTest__EnumA::class,
            ),
        );

        (new UnitEnumNormalizer())->denormalize('invalid', UnitEnumNormalizerTest__EnumA::class);
    }

    public function testDenormalizeBackedEnum(): void {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(
            sprintf(
                'The `$type` expected to be a `UnitEnum`, `%s` given.',
                UnitEnumNormalizerTest__BackedEnum::class,
            ),
        );

        (new UnitEnumNormalizer())->denormalize('backed', UnitEnumNormalizerTest__BackedEnum::class);
    }

    public function testDenormalizeAllowInvalid(): void {
        self::assertNull(
            (new UnitEnumNormalizer([UnitEnumNormalizer::ContextAllowInvalidValues => true]))->denormalize(
                'invalid',
                UnitEnumNormalizerTest__EnumA::class,
            ),
        );
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
enum UnitEnumNormalizerTest__EnumA {
    case A;
    case B;
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
enum UnitEnumNormalizerTest__EnumB {
    case B;
    case C;
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
enum UnitEnumNormalizerTest__BackedEnum: string {
    case A = 'A';
    case B = 'B';
}
