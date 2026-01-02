<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Logger\Defaults;

use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Contracts\Output as Contract;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Enums\Verbosity;
use Override;

use function mb_str_split;
use function mb_strlen;

use const PHP_EOL;

class Output implements Contract {
    public function __construct(
        /**
         * @var positive-int
         */
        public readonly int $width = 80,
        public readonly string $eol = PHP_EOL,
    ) {
        // empty
    }

    #[Override]
    public function write(string $line, Verbosity $verbosity): void {
        echo $line.$this->eol;
    }

    #[Override]
    public function length(string $string): int {
        return mb_strlen($string);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function split(string $string, int $limit): array {
        return mb_str_split($string, $limit);
    }
}
