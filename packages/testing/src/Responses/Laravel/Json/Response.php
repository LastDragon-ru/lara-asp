<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Responses\Laravel\Json;

use LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonSchema;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCode;
use LastDragon_ru\LaraASP\Testing\Responses\JsonResponse;
use LastDragon_ru\LaraASP\Testing\Utils\WithTestData;
use function is_string;

class Response extends JsonResponse {
    use WithTestData;

    /**
     * @param \LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCode    $code
     * @param \LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonSchema|string $resource
     * @param \SplFileInfo|\stdClass|array|string|null                          $content
     */
    public function __construct(StatusCode $code, $resource, $content = null) {
        if (is_string($resource)) {
            $resource = new JsonSchema($this->getTestData($resource)->file('.json'));
        }

        parent::__construct($code, $resource, $content);
    }
}
