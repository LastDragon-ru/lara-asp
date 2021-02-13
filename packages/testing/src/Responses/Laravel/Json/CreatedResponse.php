<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Responses\Laravel\Json;

use JsonSerializable;
use LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonSchema;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCodes\Created;
use SplFileInfo;
use stdClass;

class CreatedResponse extends Response {
    public function __construct(
        JsonSchema|string $resource,
        JsonSerializable|SplFileInfo|stdClass|array|string|null $content = null,
    ) {
        parent::__construct(new Created(), $resource, $content);
    }
}
