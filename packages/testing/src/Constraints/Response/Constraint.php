<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Response;

use Illuminate\Testing\TestResponse;
use InvalidArgumentException;
use LastDragon_ru\LaraASP\Testing\Constraints\JsonPrettify;
use PHPUnit\Framework\Constraint\Constraint as PHPUnitConstraint;

abstract class Constraint extends PHPUnitConstraint {
    use JsonPrettify;
    use FailureDescription;

    /**
     * @inheritdoc
     */
    public function evaluate($other, string $description = '', bool $returnResult = false): ?bool {
        if (!($other instanceof TestResponse)) {
            throw new InvalidArgumentException(sprintf('The `%s` must be instance of `%s`.', '$other', TestResponse::class));
        }

        return parent::evaluate($other, $description, $returnResult);
    }
}
