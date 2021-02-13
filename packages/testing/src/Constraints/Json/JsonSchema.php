<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Json;

use JsonSerializable;
use Opis\JsonSchema\ISchemaLoader;
use SplFileInfo;
use stdClass;

class JsonSchema {
    protected JsonSerializable|SplFileInfo|stdClass|array|string|null $schema;
    protected ?ISchemaLoader                                          $loader;

    public function __construct(
        JsonSerializable|SplFileInfo|stdClass|array|string|null $schema,
        ISchemaLoader $loader = null,
    ) {
        $this->schema = $schema;
        $this->loader = $loader ?: new JsonSchemaLoader();
    }

    public function getSchema(): SplFileInfo|stdClass|array|string|null {
        return $this->schema;
    }

    public function getLoader(): ?ISchemaLoader {
        return $this->loader;
    }
}
