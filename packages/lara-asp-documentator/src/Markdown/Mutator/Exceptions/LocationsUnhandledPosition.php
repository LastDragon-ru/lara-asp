<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\List\Position;
use Throwable;

class LocationsUnhandledPosition extends MutatorError {
    public function __construct(
        protected Position $position,
        /**
         * @var list<Location>
         */
        protected array $locations,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            'Unhandled position detected.',
            $previous,
        );
    }

    public function getPosition(): Position {
        return $this->position;
    }

    /**
     * @return list<Location>
     */
    public function getLocations(): array {
        return $this->locations;
    }
}
