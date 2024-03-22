<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Directives;

use Exception;
use LastDragon_ru\LaraASP\GraphQL\Stream\Exceptions\Client\CursorInvalidPath;
use LastDragon_ru\LaraASP\GraphQL\Stream\Offset as StreamOffset;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Mockery;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(Offset::class)]
final class OffsetTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderGetFieldArgumentValue')]
    public function testGetFieldArgumentValue(Exception|StreamOffset $expected, ResolveInfo $info, mixed $value): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $directive = new Offset();
        $actual    = $directive->getFieldArgumentValue($info, $value);

        self::assertEquals($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{Exception|StreamOffset, ResolveInfo, mixed}>
     */
    public static function dataProviderGetFieldArgumentValue(): array {
        return [
            'null'                 => [
                new StreamOffset('path.to.*.field', 0, null),
                self::getResolveInfo(['path', 'to', 1, 'field']),
                null,
            ],
            'int'                  => [
                new StreamOffset('path.to.*.field', 10, null),
                self::getResolveInfo(['path', 'to', 1, 'field']),
                10,
            ],
            'cursor'               => [
                new StreamOffset('path.to.*.field', 10, null),
                self::getResolveInfo(['path', 'to', 1, 'field']),
                new StreamOffset('path.to.*.field', 10, null),
            ],
            'cursor: invalid path' => [
                new CursorInvalidPath('path.to.*.field', 'another.field'),
                self::getResolveInfo(['path', 'to', 1, 'field']),
                new StreamOffset('another.field'),
            ],
        ];
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    /**
     * @param array<int, string|int> $path
     */
    private static function getResolveInfo(array $path): ResolveInfo {
        $info       = Mockery::mock(ResolveInfo::class);
        $info->path = $path;

        return $info;
    }
    // </editor-fold>
}
