<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Scalars;

use Exception;
use GraphQL\Error\Error;
use GraphQL\Error\InvariantViolation;
use GraphQL\Language\AST\IntValueNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Language\AST\ValueNode;
use Illuminate\Contracts\Encryption\Encrypter;
use LastDragon_ru\LaraASP\GraphQL\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQL\Stream\Offset as StreamOffset;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;
use LastDragon_ru\LaraASP\Testing\Mockery\PropertiesMock;
use LastDragon_ru\LaraASP\Testing\Mockery\WithProperties;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function is_string;

/**
 * @internal
 */
#[CoversClass(Offset::class)]
final class OffsetTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderSerialize')]
    public function testSerialize(Exception|string|int $expected, mixed $value): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $encrypter = $this->app()->make(Encrypter::class);
        $scalar    = Mockery::mock(Offset::class, new WithProperties(), PropertiesMock::class);
        $scalar->makePartial();

        if (is_string($expected)) {
            $scalar
                ->shouldUseProperty('encrypter')
                ->value($encrypter);
            $scalar
                ->shouldUseProperty('serializer')
                ->value(
                    $this->app()->make(Serializer::class),
                );
        }

        $actual = $scalar->serialize($value);
        $actual = is_string($actual)
            ? $encrypter->decrypt($actual, false)
            : $actual;

        self::assertEquals($expected, $actual);
    }

    #[DataProvider('dataProviderParseValue')]
    public function testParseValue(Exception|StreamOffset|int $expected, mixed $value): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $encrypter = $this->app()->make(Encrypter::class);
        $scalar    = Mockery::mock(Offset::class, new WithProperties(), PropertiesMock::class);
        $scalar->makePartial();

        if (is_string($value)) {
            $scalar
                ->shouldUseProperty('encrypter')
                ->value($encrypter);
            $scalar
                ->shouldUseProperty('serializer')
                ->value(
                    $this->app()->make(Serializer::class),
                );
        }

        $value  = is_string($value)
            ? $encrypter->encrypt($value, false)
            : $value;
        $actual = $scalar->parseValue($value);

        self::assertEquals($expected, $actual);
    }

    #[DataProvider('dataProviderParseLiteral')]
    public function testParseLiteral(Exception|StreamOffset|int $expected, Node&ValueNode $value): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $encrypter = $this->app()->make(Encrypter::class);
        $scalar    = Mockery::mock(Offset::class, new WithProperties(), PropertiesMock::class);
        $scalar->makePartial();

        if ($value instanceof StringValueNode) {
            $value->value = $encrypter->encrypt($value->value, false);
            $scalar
                ->shouldUseProperty('encrypter')
                ->value($encrypter);
            $scalar
                ->shouldUseProperty('serializer')
                ->value(
                    $this->app()->make(Serializer::class),
                );
        }

        $actual = $scalar->parseLiteral($value);

        self::assertEquals($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{Exception|string|int, mixed}>
     */
    public static function dataProviderSerialize(): array {
        return [
            'invalid'           => [
                new InvariantViolation('The valid cursor/offset expected, `"invalid"` given.'),
                'invalid',
            ],
            'offset (= 0)'      => [
                0,
                0,
            ],
            'offset (> 0)'      => [
                123,
                123,
            ],
            'offset (< 0)'      => [
                new InvariantViolation('The offset must be greater or equal to 0.'),
                -1,
            ],
            StreamOffset::class => [
                '{"path":"path.to.field","offset":null,"cursor":null}',
                new StreamOffset(path: 'path.to.field'),
            ],
        ];
    }

    /**
     * @return array<string, array{Exception|StreamOffset|int, mixed}>
     */
    public static function dataProviderParseValue(): array {
        return [
            'offset (= 0)'      => [
                0,
                0,
            ],
            'offset (> 0)'      => [
                123,
                123,
            ],
            'offset (< 0)'      => [
                new Error('The offset must be greater or equal to 0.'),
                -1,
            ],
            'invalid'           => [
                new Error('The cursor is not valid.'),
                'invalid',
            ],
            StreamOffset::class => [
                new StreamOffset('path.to.field'),
                '{"path":"path.to.field","offset":null}',
            ],
        ];
    }

    /**
     * @return array<string, array{Exception|StreamOffset|int, Node&ValueNode}>
     */
    public static function dataProviderParseLiteral(): array {
        return [
            'offset (= 0)'      => [
                0,
                new IntValueNode(['value' => '0']),
            ],
            'offset (> 0)'      => [
                123,
                new IntValueNode(['value' => '123']),
            ],
            'offset (< 0)'      => [
                new Error('The offset must be greater or equal to 0.'),
                new IntValueNode(['value' => '-1']),
            ],
            'invalid'           => [
                new Error('The cursor is not valid.'),
                new StringValueNode(['value' => 'invalid']),
            ],
            StreamOffset::class => [
                new StreamOffset('path.to.field'),
                new StringValueNode(['value' => '{"path":"path.to.field"}']),
            ],
        ];
    }
    // </editor-fold>
}
