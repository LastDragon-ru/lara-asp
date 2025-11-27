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
#[CoversClass(EnumNormalizer::class)]
final class EnumNormalizerTest extends TestCase {
    public function testNormalize(): void {
        $normalizer = new EnumNormalizer();
        $enumAA     = EnumNormalizerTest__UnitEnumA::A;
        $enumAB     = EnumNormalizerTest__UnitEnumA::B;
        $enumBB     = EnumNormalizerTest__UnitEnumB::B;
        $enumBC     = EnumNormalizerTest__UnitEnumB::C;
        $enumCA     = EnumNormalizerTest__BackedEnum::A;
        $enumCB     = EnumNormalizerTest__BackedEnum::B;

        self::assertSame($enumAA->name, $normalizer->normalize($enumAA));
        self::assertSame($enumAB->name, $normalizer->normalize($enumAB));
        self::assertSame($enumBB->name, $normalizer->normalize($enumAB));
        self::assertSame($enumBB->name, $normalizer->normalize($enumBB));
        self::assertSame($enumBC->name, $normalizer->normalize($enumBC));
        self::assertSame($enumCA->value, $normalizer->normalize($enumCA));
        self::assertSame($enumCB->value, $normalizer->normalize($enumCB));
    }

    public function testNormalizeNotEnum(): void {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(
            sprintf(
                'The `$object` expected to be a `UnitEnum`, `%s` given.',
                stdClass::class,
            ),
        );

        (new EnumNormalizer())->normalize(new stdClass());
    }

    public function testDenormalize(): void {
        $normalizer = new EnumNormalizer();
        $enumAA     = EnumNormalizerTest__UnitEnumA::A;
        $enumAB     = EnumNormalizerTest__UnitEnumA::B;
        $enumBB     = EnumNormalizerTest__UnitEnumB::B;
        $enumBC     = EnumNormalizerTest__UnitEnumB::C;
        $enumCA     = EnumNormalizerTest__BackedEnum::A;
        $enumCB     = EnumNormalizerTest__BackedEnum::B;

        self::assertSame($enumAA, $normalizer->denormalize($enumAA->name, $enumAA::class));
        self::assertSame($enumAB, $normalizer->denormalize($enumAB->name, $enumAB::class));
        self::assertSame($enumBB, $normalizer->denormalize($enumAB->name, $enumBB::class));
        self::assertSame($enumBB, $normalizer->denormalize($enumBB->name, $enumBB::class));
        self::assertSame($enumBC, $normalizer->denormalize($enumBC->name, $enumBC::class));
        self::assertSame($enumCA, $normalizer->denormalize($enumCA->value, $enumCA::class));
        self::assertSame($enumCB, $normalizer->denormalize($enumCB->value, $enumCB::class));
    }

    public function testDenormalizeNotEnum(): void {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(
            sprintf(
                'The `$type` expected to be a `UnitEnum`, `%s` given.',
                stdClass::class,
            ),
        );

        (new EnumNormalizer())->denormalize('value', stdClass::class);
    }

    public function testDenormalizeInvalidUnitEnum(): void {
        self::expectException(UnexpectedValueException::class);
        self::expectExceptionMessage(
            sprintf(
                'The data must belong to a enumeration of type `%s`.',
                EnumNormalizerTest__UnitEnumA::class,
            ),
        );

        (new EnumNormalizer())->denormalize('invalid', EnumNormalizerTest__UnitEnumA::class);
    }

    public function testDenormalizeInvalidBackedEnum(): void {
        self::expectException(UnexpectedValueException::class);
        self::expectExceptionMessage(
            sprintf(
                'The data must belong to a enumeration of type `%s`.',
                EnumNormalizerTest__BackedEnum::class,
            ),
        );

        (new EnumNormalizer())->denormalize('invalid', EnumNormalizerTest__BackedEnum::class);
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
enum EnumNormalizerTest__UnitEnumA {
    case A;
    case B;
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
enum EnumNormalizerTest__UnitEnumB {
    case B;
    case C;
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
enum EnumNormalizerTest__BackedEnum: string {
    case A = 'A';
    case B = 'B';
}
