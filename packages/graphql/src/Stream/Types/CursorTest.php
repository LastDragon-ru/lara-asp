<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Types;

use Exception;
use GraphQL\Error\Error;
use GraphQL\Error\InvariantViolation;
use GraphQL\Language\AST\IntValueNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Language\AST\ValueNode;
use Illuminate\Container\Container;
use Illuminate\Contracts\Encryption\Encrypter;
use LastDragon_ru\LaraASP\GraphQL\Stream\Cursor as StreamCursor;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function is_string;

/**
 * @internal
 */
#[CoversClass(Cursor::class)]
class CursorTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderSerialize
     */
    public function testSerialize(Exception|string|int $expected, mixed $value): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $scalar = new Cursor();
        $actual = $scalar->serialize($value);
        $actual = is_string($expected)
            ? Container::getInstance()->make(Encrypter::class)->decrypt($actual, false)
            : $actual;

        self::assertEquals($expected, $actual);
    }

    /**
     * @dataProvider dataProviderParseValue
     */
    public function testParseValue(Exception|StreamCursor|int $expected, mixed $value): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $value  = is_string($value)
            ? Container::getInstance()->make(Encrypter::class)->encrypt($value, false)
            : $value;
        $scalar = new Cursor();
        $actual = $scalar->parseValue($value);

        self::assertEquals($expected, $actual);
    }

    /**
     * @dataProvider dataProviderParseLiteral
     */
    public function testParseLiteral(Exception|StreamCursor|int $expected, Node&ValueNode $value): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        if ($value instanceof StringValueNode) {
            $value->value = Container::getInstance()->make(Encrypter::class)->encrypt($value->value, false);
        }

        $scalar = new Cursor();
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
                new InvariantViolation('The valid Cursor expected, `"invalid"` given.'),
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
            StreamCursor::class => [
                '{"key":"123","offset":null,"chunk":null,"where":null,"order":null}',
                new StreamCursor(key: '123'),
            ],
        ];
    }

    /**
     * @return array<string, array{Exception|StreamCursor|int, mixed}>
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
                new Error('The Cursor is not valid.'),
                'invalid',
            ],
            StreamCursor::class => [
                new StreamCursor(key: '123'),
                '{"key":"123","offset":null,"chunk":null,"where":null,"order":null}',
            ],
        ];
    }

    /**
     * @return array<string, array{Exception|StreamCursor|int, Node&ValueNode}>
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
                new Error('The Cursor is not valid.'),
                new StringValueNode(['value' => 'invalid']),
            ],
            StreamCursor::class => [
                new StreamCursor(key: '123'),
                new StringValueNode(['value' => '{"key":"123","chunk":null,"where":null,"order":null}']),
            ],
        ];
    }
    // </editor-fold>
}
