<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Json;

use Opis\JsonSchema\Uri;
use SplFileInfo;

class JsonSchemaFile implements JsonSchema {
    /**
     * @param array<string,string> $parameters
     */
    public function __construct(
        protected SplFileInfo $schema,
        protected array $parameters = [],
    ) {
        // empty
    }

    public function getSchema(): Uri {
        return Protocol::getUri($this->schema, $this->parameters);
    }
}
