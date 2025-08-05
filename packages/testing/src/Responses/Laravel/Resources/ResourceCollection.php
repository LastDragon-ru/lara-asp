<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Responses\Laravel\Resources;

use LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonSchemaFile;
use LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonSchemaWrapper;
use LastDragon_ru\LaraASP\Testing\Utils\WithTestData;
use SplFileInfo;

use function is_string;

class ResourceCollection extends JsonSchemaWrapper {
    use WithTestData;

    /**
     * @param JsonSchemaWrapper|JsonSchemaFile|SplFileInfo|class-string $schema
     */
    public function __construct(
        JsonSchemaWrapper|JsonSchemaFile|SplFileInfo|string $schema,
    ) {
        if (is_string($schema)) {
            $schema = self::getTestData($schema)->file('.json');
        }

        parent::__construct($schema, self::getTestData()->file('.json'));
    }
}
