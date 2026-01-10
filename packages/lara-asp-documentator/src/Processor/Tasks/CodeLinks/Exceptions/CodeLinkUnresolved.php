<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Exceptions;

use Throwable;

use function implode;
use function sprintf;

class CodeLinkUnresolved extends CodeLinkError {
    public function __construct(
        /**
         * @var non-empty-list<string>
         */
        public array $unresolved,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'The following unresolved code links have been found: %1$s.',
                '`'.implode('`, `', $this->unresolved).'`',
            ),
            $previous,
        );
    }

    /**
     * @return non-empty-list<string>
     */
    public function getUnresolved(): array {
        return $this->unresolved;
    }
}
