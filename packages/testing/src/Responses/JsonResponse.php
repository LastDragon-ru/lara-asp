<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Responses;

use LastDragon_ru\LaraASP\Testing\Constraints\JsonMatchesSchema;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\Bodies\JsonBody;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentTypes\JsonContentType;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\Response;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCode;
use LastDragon_ru\LaraASP\Testing\Utils\Args;
use PHPUnit\Framework\Constraint\JsonMatches;
use SplFileInfo;
use function json_encode;

class JsonResponse extends Response {
    /**
     * @param \LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCode $code
     * @param \SplFileInfo                                                   $schema
     * @param \SplFileInfo|\stdClass|array|string|null                       $content
     */
    public function __construct(StatusCode $code, SplFileInfo $schema, $content = null) {
        if ($content) {
            $content = Args::getJson($content) ?? Args::invalidJson();
        }

        parent::__construct(
            $code,
            new JsonContentType(),
            new JsonBody(...array_filter([
                new JsonMatchesSchema($schema),
                $content
                    ? new JsonMatches(json_encode($content))
                    : null,
            ])),
        );
    }
}
