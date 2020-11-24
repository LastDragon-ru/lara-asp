<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCodes;

use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCode;

class Ok extends StatusCode {
    public function __construct() {
        parent::__construct(200);
    }
}
