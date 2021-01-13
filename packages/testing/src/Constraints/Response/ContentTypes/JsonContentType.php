<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentTypes;

use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentType;

class JsonContentType extends ContentType {
    public function __construct() {
        parent::__construct('application/json');
    }
}
