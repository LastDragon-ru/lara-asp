<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Response;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Testing\Constraints\JsonPrettify;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeExpectedImpl;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeExpectedInterface;
use PHPUnit\Framework\Constraint\Constraint as PHPUnitConstraint;
use Psr\Http\Message\ResponseInterface;
use function sprintf;

abstract class Constraint extends PHPUnitConstraint implements CompositeExpectedInterface {
    use JsonPrettify;
    use CompositeExpectedImpl;

    /**
     * @inheritdoc
     *
     * @param \Psr\Http\Message\ResponseInterface $other
     * @param string                              $description
     * @param bool                                $returnResult
     *
     * @return bool|null
     */
    public function evaluate($other, string $description = '', bool $returnResult = false): ?bool {
        if (!($other instanceof ResponseInterface)) {
            throw new InvalidArgumentException(sprintf('The `$other` must be instance of `%s`.', ResponseInterface::class));
        }

        return parent::evaluate($other, $description, $returnResult);
    }
}
