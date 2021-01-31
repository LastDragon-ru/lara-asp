<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Responses\Laravel\Json;

use LastDragon_ru\LaraASP\Testing\Constraints\JsonMatchesSchema;
use LastDragon_ru\LaraASP\Testing\Constraints\JsonSchema;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\Bodies\JsonBody;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentTypes\JsonContentType;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\Response;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCodes\UnprocessableEntity;
use LastDragon_ru\LaraASP\Testing\Utils\WithTestData;
use function array_filter;
use function array_keys;
use function array_unique;
use function count;
use function is_array;
use function is_null;
use const SORT_REGULAR;

class ValidationErrorResponse extends Response {
    use WithTestData;

    public function __construct(array $errors = null) {
        parent::__construct(
            new UnprocessableEntity(),
            new JsonContentType(),
            new JsonBody(...array_filter([
                new JsonMatchesSchema(new JsonSchema($this->getTestData(self::class)->file('.json'))),
                $errors
                    ? new JsonMatchesSchema(new JsonSchema($this->getErrorsSchema($errors)))
                    : null,
            ])),
        );
    }

    protected function getErrorsSchema(array $errors): array {
        $properties = [];

        foreach ($errors as $key => $error) {
            if (is_null($error) || (is_array($error) && empty($error))) {
                $properties[$key] = [
                    'type'     => 'array',
                    'minItems' => 1,
                    'items'    => [
                        'type' => 'string',
                    ],
                ];
            } else {
                $enum             = array_unique((array) $error, SORT_REGULAR);
                $properties[$key] = [
                    'type'     => 'array',
                    'minItems' => count($enum),
                    'items'    => [
                        'type' => 'string',
                        'enum' => $enum,
                    ],
                ];
            }
        }

        return [
            '$schema'    => 'http://json-schema.org/draft-07/schema#',
            'type'       => 'object',
            'required'   => [
                'errors',
            ],
            'properties' => [
                'errors' => [
                    'type'       => 'object',
                    'required'   => array_keys($properties),
                    'properties' => $properties,
                ],
            ],
        ];
    }
}
