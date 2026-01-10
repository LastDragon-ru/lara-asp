<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Delete;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Replace;
use Throwable;

class MutagensCannotBeMerged extends MutatorError {
    public function __construct(
        /**
         * @var list<Replace|Delete>
         */
        protected array $mutagens,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            'Mutagens cannot be merged.',
            $previous,
        );
    }

    /**
     * @return list<Replace|Delete>
     */
    public function getMutagens(): array {
        return $this->mutagens;
    }
}
