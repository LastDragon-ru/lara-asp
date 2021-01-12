<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Response;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Testing\Constraints\JsonPrettify;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeExpectedImpl;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeExpectedInterface;
use PHPUnit\Framework\Constraint\Constraint as PHPUnitConstraint;
use Psr\Http\Message\ResponseInterface;
use function explode;
use function implode;
use function in_array;
use function mb_strtolower;
use function sprintf;
use function str_ends_with;
use function str_starts_with;
use const PHP_EOL;

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

    /**
     * @param \Psr\Http\Message\ResponseInterface $other
     *
     * @return string
     */
    protected function additionalFailureDescription($other): string {
        $contentType = mb_strtolower(explode(';', $other->getHeaderLine('Content-Type'))[0]);
        $isText      = false
            || str_starts_with($contentType, 'text/')   // text
            || str_ends_with($contentType, '+xml')      // xml based
            || str_ends_with($contentType, '+json')     // json based
            || in_array($contentType, [                 // other
                'application/json',
            ], true);
        $description = $isText
            ? PHP_EOL.((string) $other->getBody())
            : '';

        return $description;
    }
}
