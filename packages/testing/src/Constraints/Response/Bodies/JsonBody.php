<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Response\Bodies;

use LastDragon_ru\LaraASP\Testing\Constraints\Response\Body;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsJson;

class JsonBody extends Body {
    public function __construct(Constraint ...$constraints) {
        parent::__construct(new IsJson(), ...$constraints);
    }
}
