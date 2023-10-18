<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Directives;

use Exception;
use LastDragon_ru\LaraASP\GraphQL\Stream\Cursor as StreamCursor;
use LastDragon_ru\LaraASP\GraphQL\Stream\Exceptions\Client\CursorInvalidPath;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Mockery;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Cursor::class)]
class CursorTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderGetFieldArgumentValue
     */
    public function testGetFieldArgumentValue(Exception|StreamCursor $expected, ResolveInfo $info, mixed $value): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $directive = new Cursor();
        $actual    = $directive->getFieldArgumentValue($info, $value);

        self::assertEquals($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{Exception|StreamCursor, ResolveInfo, mixed}>
     */
    public static function dataProviderGetFieldArgumentValue(): array {
        return [
            'null'                 => [
                new StreamCursor('path.to.*.field', null, 0),
                self::getResolveInfo(['path', 'to', 1, 'field']),
                null,
            ],
            'int'                  => [
                new StreamCursor('path.to.*.field', null, 10),
                self::getResolveInfo(['path', 'to', 1, 'field']),
                10,
            ],
            'cursor'               => [
                new StreamCursor('path.to.*.field', null, 10),
                self::getResolveInfo(['path', 'to', 1, 'field']),
                new StreamCursor('path.to.*.field', null, 10),
            ],
            'cursor: invalid path' => [
                new CursorInvalidPath('path.to.*.field', 'another.field'),
                self::getResolveInfo(['path', 'to', 1, 'field']),
                new StreamCursor('another.field'),
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