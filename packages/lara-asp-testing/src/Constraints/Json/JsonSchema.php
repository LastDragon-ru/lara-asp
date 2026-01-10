<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Json;

use Opis\JsonSchema\Schema;
use Opis\JsonSchema\Uri;
use stdClass;

interface JsonSchema {
    public function getSchema(): Uri|Schema|stdClass|string;
}
