<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Json;

use Opis\JsonSchema\Uri;
use Override;
use SplFileInfo;

use function array_merge;

class JsonSchemaWrapper implements JsonSchema {
    protected Uri $schema;

    /**
     * @param array<string,string> $rootParameters
     */
    public function __construct(
        self|JsonSchemaFile|SplFileInfo $schema,
        SplFileInfo $rootSchema,
        array $rootParameters = [],
    ) {
        if ($schema instanceof JsonSchemaFile || $schema instanceof self) {
            $schema = $schema->getSchema();
        } else {
            $schema = Protocol::getUri($schema);
        }

        $this->schema = Protocol::getUri($rootSchema, array_merge($rootParameters, [
            'schema.path' => (string) $schema,
        ]));
    }

    #[Override]
    public function getSchema(): Uri {
        return $this->schema;
    }
}
