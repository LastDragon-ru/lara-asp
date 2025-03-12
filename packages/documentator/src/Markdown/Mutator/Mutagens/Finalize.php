<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens;

use Closure;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;

readonly class Finalize {
    public function __construct(
        /**
         * @var Closure(Document): void
         */
        protected Closure $closure,
    ) {
        // empty
    }

    public function __invoke(Document $document): void {
        ($this->closure)($document);
    }
}
