<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http\Resources;

use Illuminate\Pagination\AbstractPaginator;

class PaginatedCollection extends ResourceCollection {
    /**
     * @param string                                   $class
     * @param \Illuminate\Pagination\AbstractPaginator $resource
     */
    public function __construct(string $class, AbstractPaginator $resource) {
        parent::__construct($class, $resource);
    }
}
