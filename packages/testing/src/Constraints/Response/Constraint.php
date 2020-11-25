<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Response;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Testing\Constraints\JsonPrettify;
use PHPUnit\Framework\Constraint\Constraint as PHPUnitConstraint;
use Psr\Http\Message\ResponseInterface;

abstract class Constraint extends PHPUnitConstraint {
    use JsonPrettify;

    /**
     * @inheritdoc
     */
    public function evaluate($other, string $description = '', bool $returnResult = false): ?bool {
        if (!($other instanceof ResponseInterface)) {
            throw new InvalidArgumentException(sprintf('The `%s` must be instance of `%s`.', '$other', ResponseInterface::class));
        }

        return parent::evaluate($other, $description, $returnResult);
    }
}
