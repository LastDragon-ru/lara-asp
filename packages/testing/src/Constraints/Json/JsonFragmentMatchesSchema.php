<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Json;

use Illuminate\Support\Arr;
use LastDragon_ru\LaraASP\Testing\Utils\Args;

use function is_array;
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
    public function evaluate(mixed $other, string $description = '', bool $returnResult = false): ?bool {
        $actual = Args::getJson($other, true);
        $actual = is_array($actual) ? Arr::get($actual, $this->path) : null;
        $result = parent::evaluate($actual, $description, $returnResult);

        return $result;
    }

    public function toString(): string {
        return sprintf(
            'fragment "%s" matches JSON schema',
            $this->path,
        );
    }
    // </editor-fold>
}
