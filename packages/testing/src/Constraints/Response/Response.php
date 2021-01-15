<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Response;

use PHPUnit\Framework\Constraint\Constraint as PHPUnitConstraint;
use PHPUnit\Framework\Constraint\LogicalAnd;
use Psr\Http\Message\ResponseInterface;
use function array_filter;
use function array_map;
use function array_unique;
use function get_class;
use function implode;
use function is_null;
use const PHP_EOL;

class Response extends Constraint {
    /**
     * @var array|\PHPUnit\Framework\Constraint\Constraint[]
     */
    protected array              $constraints;
    protected ?PHPUnitConstraint $failed = null;

    public function __construct(PHPUnitConstraint ...$constraints) {
        $this->constraints = $constraints;
    }

    // <editor-fold desc="Constraint">
    // =========================================================================
    /**
     * @return array|\PHPUnit\Framework\Constraint\Constraint[]
     */
    public function getConstraints(): array {
        return $this->constraints;
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $other
     *
     * @return bool
     */
    protected function matches($other): bool {
        $matches      = true;
        $this->failed = null;

        foreach ($this->getConstraints() as $constraint) {
            if (!$this->isConstraintMatches($other, $constraint)) {
                $matches      = false;
                $this->failed = $constraint;
                break;
            }
        }

        return $matches;
    }

    public function toString(): string {
        return is_null($this->failed)
            ? LogicalAnd::fromConstraints(...$this->getConstraints())->toString()
            : $this->failed->toString();
    }

    protected function additionalFailureDescription($other): string {
        $base        = self::class === get_class($this)
            ? parent::additionalFailureDescription($other)
            : '';
        $failed      = $this->failed
            ? $this->failed->additionalFailureDescription($other)
            : '';
        $description = [$failed, $base];
        $description = array_map(function (string $text) {
            return trim($text, PHP_EOL);
        }, $description);
        $description = array_unique($description);
        $description = array_filter($description);
        $description = PHP_EOL.implode(PHP_EOL.PHP_EOL, $description).PHP_EOL;

        return $description;
    }
    // </editor-fold>

    // <editor-fold desc="Functions">
    // =========================================================================
    protected function isConstraintMatches(ResponseInterface $other, PHPUnitConstraint $constraint): bool {
        return $constraint->evaluate($other, '', true);
    }
    // </editor-fold>
}
