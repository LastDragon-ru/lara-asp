<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Ast;

use LastDragon_ru\DiyParser\Ast\Cursor;
use LastDragon_ru\GlobMatcher\Package;
use Override;

use function abs;
use function filter_var;
use function max;
use function mb_ltrim;
use function mb_str_pad;
use function mb_strlen;
use function mb_strpos;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_INT;
use const STR_PAD_LEFT;

class IntegerSequenceNode extends IncrementalSequenceNode {
    public function __construct(
        /**
         * @var numeric-string
         */
        public string $start,
        /**
         * @var numeric-string
         */
        public string $end,
        public ?int $increment = null,
    ) {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function toIterable(Cursor $cursor): iterable {
        $start  = static::parse($cursor->node->start);
        $end    = static::parse($cursor->node->end);
        $inc    = abs($cursor->node->increment ?? 1);
        $inc    = $start < $end ? $inc : -$inc;
        $steps  = abs(($end - $start) / $inc);
        $length = $cursor->node->start !== (string) $start || $cursor->node->end !== (string) $end
            ? max(
                mb_strlen($cursor->node->start, Package::Encoding),
                mb_strlen($cursor->node->end, Package::Encoding),
            )
            : 0;

        for ($value = $start, $step = 0; $step <= $steps; $step++, $value += $inc) {
            yield $value < 0
                ? '-'.mb_str_pad((string) abs($value), $length - 1, '0', STR_PAD_LEFT, Package::Encoding)
                : mb_str_pad((string) $value, $length, '0', STR_PAD_LEFT, Package::Encoding);
        }
    }

    protected static function parse(string $string): int {
        $negative = mb_strpos($string, '-', 0, Package::Encoding) === 0;
        $trimmed  = mb_ltrim($string, '-0', Package::Encoding);
        $trimmed  = ($negative ? '-' : '').($trimmed !== '' ? $trimmed : '0');
        $integer  = (int) filter_var($trimmed, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

        return $integer;
    }
}
