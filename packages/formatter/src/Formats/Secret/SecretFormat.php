<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Formats\Secret;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Formatter\Contracts\Format;
use Override;

use function mb_str_pad;
use function mb_strlen;
use function mb_substr;
use function str_replace;

/**
 * @implements Format<SecretOptions, string|null>
 */
readonly class SecretFormat implements Format {
    protected int $visible;

    /**
     * @param list<SecretOptions|null> $options
     */
    public function __construct(array $options = []) {
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

    #[Override]
    public function __invoke(mixed $value): string {
        // Null?
        if ($value === null) {
            return '';
        }

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
