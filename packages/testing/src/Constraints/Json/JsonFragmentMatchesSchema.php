<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Json;

use Illuminate\Support\Arr;
use LastDragon_ru\LaraASP\Testing\Utils\Args;

use function sprintf;

/**
 * Check that JSON fragment matches schema.
 *
 * @see https://json-schema.org/
 * @see https://github.com/opis/json-schema
 */
class JsonFragmentMatchesSchema extends JsonMatchesSchema {
    public function __construct(
        protected string $path,
        JsonSchema $schema,
    ) {
        parent::__construct($schema);
    }

    // <editor-fold desc="\PHPUnit\Framework\Constraint\Constraint">
    // =========================================================================
    /**
     * @inheritdoc
     */
    public function evaluate($other, string $description = '', bool $returnResult = false): ?bool {
        return parent::evaluate(
            Arr::get(Args::getJson($other, true), $this->path),
            $description,
            $returnResult,
        );
    }

    public function toString(): string {
        return sprintf(
            'fragment "%s" matches JSON schema',
            $this->path,
        );
    }
    // </editor-fold>
}
