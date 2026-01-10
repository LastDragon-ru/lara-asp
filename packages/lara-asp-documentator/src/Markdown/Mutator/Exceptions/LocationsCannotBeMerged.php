<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location;
use Throwable;

class LocationsCannotBeMerged extends MutatorError {
    public function __construct(
        /**
         * @var list<Location>
         */
        protected array $locations,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            'Locations cannot be merged.',
            $previous,
        );
    }

    /**
     * @return list<Location>
     */
    public function getLocations(): array {
        return $this->locations;
    }
}
