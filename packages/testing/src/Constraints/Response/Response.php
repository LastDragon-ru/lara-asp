<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Response;

use LastDragon_ru\LaraASP\Testing\Utils\Args;
use Override;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\LogicalAnd;
use PHPUnit\Framework\ExpectationFailedException;
use Psr\Http\Message\ResponseInterface;

use function array_filter;
use function array_map;
use function array_unique;
use function array_values;
use function explode;
use function implode;
use function in_array;
use function is_null;
use function mb_strtolower;
use function mb_trim;
use function str_ends_with;
use function str_starts_with;

use const PHP_EOL;

class Response extends Constraint {
    /**
     * @var array<array-key, Constraint>
     */
    protected array       $constraints;
    protected ?Constraint $failed = null;

    public function __construct(Constraint ...$constraints) {
        $this->constraints = $constraints;
    }

    /**
     * @return array<array-key, Constraint>
     */
    public function getConstraints(): array {
        return $this->constraints;
    }

    // <editor-fold desc="\PHPUnit\Framework\Constraint\Constraint">
    // =========================================================================
    #[Override]
    public function evaluate(mixed $other, string $description = '', bool $returnResult = false): ?bool {
        $other        = Args::getResponse($other);
        $success      = $this->matches($other);
        $comparison   = null;
        $this->failed = null;

        if ($success) {
            foreach ($this->getConstraints() as $constraint) {
                try {
                    if ($this->isConstraintMatches($other, $constraint, $returnResult) === false) {
                        $success      = false;
                        $this->failed = $constraint;
                        break;
                    }
                } catch (ExpectationFailedException $exception) {
                    $success      = false;
                    $comparison   = $exception->getComparisonFailure();
                    $this->failed = $constraint;
                    break;
                }
            }
        }

        if ($returnResult) {
            return $success;
        }

        if (!$success) {
            $this->fail($other, $description, $comparison);
        }

        return null;
    }

    #[Override]
    protected function matches(mixed $other): bool {
        return true;
    }

    #[Override]
    public function toString(): string {
        return is_null($this->failed)
            ? LogicalAnd::fromConstraints(...array_values($this->getConstraints()))->toString()
            : $this->failed->toString();
    }

    #[Override]
    protected function additionalFailureDescription(mixed $other, bool $root = true): string {
        if (!$other instanceof ResponseInterface) {
            return '';
        }

        $description = [];

        if ($this->failed !== null) {
            $description[] = $this->failed instanceof self
                ? $this->failed->additionalFailureDescription($other, false)
                : $this->failed->additionalFailureDescription($other);
        }

        if ($root) {
            $description[] = $this->getResponseDescription($other);
        }

        $description = array_map(static function (string $text): string {
            return mb_trim($text, PHP_EOL);
        }, $description);
        $description = array_unique($description);
        $description = array_filter($description, static fn ($line) => $line !== '');
        $description = $description !== []
            ? PHP_EOL.implode(PHP_EOL.PHP_EOL, $description).PHP_EOL
            : '';

        return $description;
    }
    // </editor-fold>

    // <editor-fold desc="Functions">
    // =========================================================================
    protected function isConstraintMatches(
        ResponseInterface $other,
        Constraint $constraint,
        bool $return = false,
    ): ?bool {
        return $constraint->evaluate($other, '', $return);
    }

    protected function getResponseDescription(ResponseInterface $response): string {
        $contentType = mb_strtolower(explode(';', $response->getHeaderLine('Content-Type'))[0]);
        $isText      = str_starts_with($contentType, 'text/')   // text
            || str_ends_with($contentType, '+xml')              // xml based
            || str_ends_with($contentType, '+json')             // json based
            || in_array($contentType, [                         // other
                'application/json',
            ], true);
        $description = $isText
            ? PHP_EOL.((string) $response->getBody())
            : '';

        return $description;
    }
    // </editor-fold>
}
