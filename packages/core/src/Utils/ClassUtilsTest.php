<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Utils;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Core\Utils\ClassUtils
 */
class ClassUtilsTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::getConstants
     *
     * @dataProvider dataProviderGetConstants
     */
    public function testGetConstants(array $expected, object|string $class, ?string $prefix, bool $recursive): void {
        $this->assertEqualsCanonicalizing($expected, ClassUtils::getConstants($class, $prefix, $recursive));
    }

    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    public function dataProviderGetConstants(): array {
        return [
            'class - without prefix - non recursive' => [
                ['A', 'PUBLIC_A'],
                ClassUtilsTest__testGetConstants::class,
                null,
                false,
            ],
            'class - with prefix - non recursive'    => [
                ['PUBLIC_A'],
                ClassUtilsTest__testGetConstants::class,
                'PUBLIC_',
                false,
            ],
            'child - without prefix - non recursive' => [
                ['PUBLIC_B'],
                ClassUtilsTest__testGetConstants__Child::class,
                null,
                false,
            ],
            'child - without prefix - recursive'     => [
                ['A', 'PUBLIC_A', 'PUBLIC_B'],
                ClassUtilsTest__testGetConstants__Child::class,
                null,
                true,
            ],
            'child - with prefix - recursive'        => [
                ['PUBLIC_A', 'PUBLIC_B'],
                ClassUtilsTest__testGetConstants__Child::class,
                'PUBLIC_',
                true,
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
class ClassUtilsTest__testGetConstants {
    public const    A           = 'A';
    public const    PUBLIC_A    = 'PUBLIC_A';
    protected const PROTECTED_A = 'PROTECTED_A';
    private const   PRIVATE_A   = 'PRIVATE_A';
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ClassUtilsTest__testGetConstants__Child extends ClassUtilsTest__testGetConstants {
    public const    PUBLIC_B    = 'PUBLIC_B';
    protected const PROTECTED_B = 'PROTECTED_B';
    private const   PRIVATE_B   = 'PRIVATE_B';
}

// @phpcs:enable
