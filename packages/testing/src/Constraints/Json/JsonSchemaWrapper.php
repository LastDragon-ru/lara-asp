<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Json;

use Opis\JsonSchema\Uri;
use SplFileInfo;

use function array_merge;

class JsonSchemaWrapper implements JsonSchema {
    protected Uri $schema;

    /**
     * @param array<string,string> $rootParameters
     */
    public function __construct(
        JsonSchemaWrapper|JsonSchemaFile|SplFileInfo $schema,
        SplFileInfo $rootSchema,
        array $rootParameters = [],
    ) {
        if ($schema instanceof JsonSchemaFile || $schema instanceof self) {
            $schema = $schema->getSchema();
        } elseif ($schema instanceof SplFileInfo) {
            $schema = Protocol::getUri($schema);
        } else {
            // empty
        }

        $this->schema = Protocol::getUri($rootSchema, array_merge($rootParameters, [
            'schema.path' => (string) $schema,
        ]));
    }

    public function getSchema(): Uri {
        return $this->schema;
    }
}
