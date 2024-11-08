<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Formatters\Secret;

use InvalidArgumentException;

use function mb_str_pad;
use function mb_strlen;
use function mb_substr;
use function str_replace;

/**
 * @internal
 */
class Formatter {
    protected readonly int $visible;

    public function __construct(?Options ...$options) {
        // Collect options
        $visible = null;

        foreach ($options as $intl) {
            if ($intl === null) {
                continue;
            }

            $visible ??= $intl->visible;
        }

        // Possible?
        if ($visible === null) {
            throw new InvalidArgumentException('The `$visible` in unknown.');
        }

        // Save
        $this->visible = $visible;
    }

    public function format(string $value): string {
        // Format
        $visible   = $this->visible;
        $length    = mb_strlen($value);
        $hidden    = $length - $visible;
        $formatted = match (true) {
            $length <= $visible => mb_str_pad('*', $length, '*'),
            $hidden < $visible  => str_replace(mb_substr($value, 0, $visible), mb_str_pad('*', $visible, '*'), $value),
            default             => str_replace(mb_substr($value, 0, $hidden), mb_str_pad('*', $hidden, '*'), $value),
        };

        // Return
        return $formatted;
    }
}
