<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Responses\Laravel\Json;

use LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonMatchesSchema;
use LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonSchema;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\Bodies\JsonBody;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentTypes\JsonContentType;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\Response;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCode;
use LastDragon_ru\LaraASP\Testing\Utils\WithTestData;

class ErrorResponse extends Response {
    use WithTestData;

    public function __construct(StatusCode $statusCode) {
        parent::__construct(
            $statusCode,
            new JsonContentType(),
            new JsonBody(
                new JsonMatchesSchema(new JsonSchema($this->getTestData(self::class)->file('.json'))),
            ),
        );
    }
}
