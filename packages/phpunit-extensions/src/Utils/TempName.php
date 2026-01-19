<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Utils;

use IteratorAggregate;
use LastDragon_ru\PhpUnit\Package;
use Override;
use Random\Randomizer;
use Traversable;

/**
 * @internal
 * @implements IteratorAggregate<mixed, non-empty-string>
 */
readonly class TempName implements IteratorAggregate {
    public function __construct(
        /**
         * @var positive-int
         */
        public int $count = 5,
        /**
         * Same as `tempnam()` on my test machine :)
         *
         * @var positive-int
         */
        public int $length = 19,
        /**
         * @var non-empty-string
         */
        public string $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
    ) {
        // empty
    }

    #[Override]
    public function getIterator(): Traversable {
        $randomizer = new Randomizer();

        for ($i = 0; $i < $this->count; $i++) {
            yield Package::Name.'-'.$randomizer->getBytesFromString($this->characters, $this->length);
        }
    }
}
