<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Responses\Laravel\Json;

use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCodes\Ok;

class OkResponse extends Response {
    /**
     * @param \LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonSchema|string $resource
     * @param \SplFileInfo|\stdClass|array|string|null                          $content
     */
    public function __construct($resource, $content = null) {
        parent::__construct(new Ok(), $resource, $content);
    }
}
