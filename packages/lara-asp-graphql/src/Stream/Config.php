<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream;

use LastDragon_ru\LaraASP\Core\Application\Configuration\Configuration;
use LastDragon_ru\LaraASP\GraphQL\Stream\Config\Limit;
use LastDragon_ru\LaraASP\GraphQL\Stream\Config\Offset;
use LastDragon_ru\LaraASP\GraphQL\Stream\Config\Search;
use LastDragon_ru\LaraASP\GraphQL\Stream\Config\Sort;

class Config extends Configuration {
    public function __construct(
        public Search $search = new Search(),
        public Sort $sort = new Sort(),
        public Limit $limit = new Limit(),
        public Offset $offset = new Offset(),
    ) {
        parent::__construct();
    }
}
