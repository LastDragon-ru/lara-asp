<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Responses\Laravel\Json;

use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCode;
use LastDragon_ru\LaraASP\Testing\Responses\JsonResponse;
use LastDragon_ru\LaraASP\Testing\Utils\WithTestData;

class Response extends JsonResponse {
    use WithTestData;

    /**
     * @param \LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCode $code
     * @param string                                                         $resource
     * @param \SplFileInfo|\stdClass|array|string|null                       $content
     */
    public function __construct(StatusCode $code, string $resource, $content = null) {
        parent::__construct($code, $this->getTestData($resource)->file('.json'), $content);
    }
}
