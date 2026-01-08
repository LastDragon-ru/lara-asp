<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Tokenizer;

use Override;
use Stringable;
use UnitEnum;

/**
 * @template-covariant TName of UnitEnum
 */
readonly class Token implements Stringable {
    public function __construct(
        /**
         * @var TName|null
         */
        public ?UnitEnum $name,
        public string $value,
        public int $offset,
    ) {
        // empty
    }

    public function is(?UnitEnum $name): bool {
        return $this->name === $name;
    }

    #[Override]
    public function __toString(): string {
        return $this->value;
    }
}
