<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Responses\Laravel\Json;

use JsonSerializable;
use LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonSchema;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCodes\Ok;
use SplFileInfo;
use stdClass;

class OkResponse extends Response {
    /**
     * @param JsonSchema|class-string $resource
     * @param JsonSerializable|SplFileInfo|stdClass|array<array-key, mixed>|string|int|float|bool|null $content
     */
    public function __construct(
        JsonSchema|string $resource,
        JsonSerializable|SplFileInfo|stdClass|array|string|int|float|bool|null $content = null,
    ) {
        parent::__construct(new Ok(), $resource, $content);
    }
}
