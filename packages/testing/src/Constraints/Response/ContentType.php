<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Response;

use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\Constraint\LogicalOr;
use PHPUnit\Framework\Constraint\StringStartsWith;

class ContentType extends Header {
    public function __construct(string $contentType) {
        parent::__construct('Content-Type', [LogicalOr::fromConstraints(
            new IsEqual($contentType),
            new StringStartsWith("{$contentType};")
        )]);
    }
}
