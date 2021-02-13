<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Responses\Laravel\Json;

use JsonSerializable;
use LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonSchema;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCode;
use LastDragon_ru\LaraASP\Testing\Responses\JsonResponse;
use LastDragon_ru\LaraASP\Testing\Utils\WithTestData;
use SplFileInfo;
use stdClass;

use function is_string;

class Response extends JsonResponse {
    use WithTestData;

    public function __construct(
        StatusCode $code,
        JsonSchema|string $resource,
        JsonSerializable|SplFileInfo|stdClass|array|string|null $content = null,
    ) {
        if (is_string($resource)) {
            $resource = new JsonSchema($this->getTestData($resource)->file('.json'));
        }

        parent::__construct($code, $resource, $content);
    }
}
