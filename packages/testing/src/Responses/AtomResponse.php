<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Responses;

use LastDragon_ru\LaraASP\Testing\Constraints\Response\Body;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentTypes\AtomContentType;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\Response;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCodes\Ok;
use LastDragon_ru\LaraASP\Testing\Constraints\Xml\XmlMatchesSchema;
use LastDragon_ru\LaraASP\Testing\Utils\WithTestData;

class AtomResponse extends Response {
    use WithTestData;

    public function __construct() {
        parent::__construct(
            new Ok(),
            new AtomContentType(),
            new Body(
                new XmlMatchesSchema($this->getTestData(self::class)->file('.rng')),
            ),
        );
    }
}
