<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Testing\Http\Resources;

use LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonSchemaFile;
use LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonSchemaWrapper;
use LastDragon_ru\LaraASP\Testing\Utils\WithTestData;
use SplFileInfo;

use function is_string;

class ResourceCollection extends JsonSchemaWrapper {
    use WithTestData;

    /**
     * @param \LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonSchemaWrapper
     *      |\LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonSchemaFile
     *      |\SplFileInfo
     *      |class-string $schema
     */
    public function __construct(
        JsonSchemaWrapper|JsonSchemaFile|SplFileInfo|string $schema,
    ) {
        if (is_string($schema)) {
            $schema = $this->getTestData($schema)->file('.json');
        }

        parent::__construct($schema, $this->getTestData()->file('.json'));
    }
}
