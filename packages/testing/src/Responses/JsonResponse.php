<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Responses;

use LastDragon_ru\LaraASP\Testing\Constraints\JsonMatchesSchema;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\Body;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentTypes\JsonContentType;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\Response;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCode;
use SplFileInfo;

class JsonResponse extends Response {
    public function __construct(StatusCode $code, SplFileInfo $schema) {
        parent::__construct(
            $code,
            new JsonContentType(),
            new Body(
                new JsonMatchesSchema($schema)
            ),
        );
    }
}
