<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Json;

use JsonSerializable;
use LastDragon_ru\LaraASP\Testing\Exceptions\InvalidArgumentJsonSchema;
use LastDragon_ru\LaraASP\Testing\Utils\Args;
use stdClass;

class JsonSchemaValue implements JsonSchema {
    protected stdClass $schema;

    /**
     * @param JsonSerializable|stdClass|array<mixed>|string $json
     */
    public function __construct(JsonSerializable|stdClass|array|string $json) {
        $schema = Args::getJson($json);

        if (!($schema instanceof stdClass)) {
            throw new InvalidArgumentJsonSchema('$schema', $json);
        }

        $this->schema = $schema;
    }

    public function getSchema(): stdClass {
        return $this->schema;
    }
}
