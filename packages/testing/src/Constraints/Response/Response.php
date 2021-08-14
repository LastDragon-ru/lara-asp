<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Response;

use LastDragon_ru\LaraASP\Testing\Utils\Args;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\LogicalAnd;
use Psr\Http\Message\ResponseInterface;

use function array_filter;
use function array_map;
use function array_unique;
use function explode;
use function implode;
use function in_array;
use function is_null;
use function mb_strtolower;
use function str_ends_with;
use function str_starts_with;
use function trim;

use const PHP_EOL;

class Response extends Constraint {
    /**
     * @var array<Constraint>
     */
    protected array       $constraints;
    protected ?Constraint $failed = null;

    public function __construct(Constraint ...$constraints) {
        $this->constraints = $constraints;
    }

    /**
     * @return array<Constraint>
     */
    public function getConstraints(): array {
        return $this->constraints;
    }

    // <editor-fold desc="\PHPUnit\Framework\Constraint\Constraint">
    // =========================================================================
    public function evaluate(mixed $other, string $description = '', bool $returnResult = false): ?bool {
        return parent::evaluate(
            Args::getResponse($other),
            $description,
            $returnResult,
        );
    }

    /**
     * @inheritdoc
     */
    protected function matches($other): bool {
        $matches      = true;
        $this->failed = null;

        if ($other instanceof ResponseInterface) {
            foreach ($this->getConstraints() as $constraint) {
                if (!$this->isConstraintMatches($other, $constraint)) {
                    $matches      = false;
                    $this->failed = $constraint;
                    break;
                }
            }
        } else {
            $matches = false;
        }

        return $matches;
    }

    public function toString(): string {
        return is_null($this->failed)
            ? LogicalAnd::fromConstraints(...$this->getConstraints())->toString()
            : $this->failed->toString();
    }

    /**
     * @inheritdoc
     */
    protected function additionalFailureDescription($other, bool $root = true): string {
        if (!$other instanceof ResponseInterface) {
            return '';
        }

        $description = [];

        if ($this->failed) {
            $description[] = $this->failed instanceof Response
                ? $this->failed->additionalFailureDescription($other, false)
                : $this->failed->additionalFailureDescription($other);
        }

        if ($root) {
            $description[] = $this->getResponseDescription($other);
        }

        $description = array_map(static function (string $text) {
            return trim($text, PHP_EOL);
        }, $description);
        $description = array_unique($description);
        $description = array_filter($description);
        $description = $description
            ? PHP_EOL.implode(PHP_EOL.PHP_EOL, $description).PHP_EOL
            : '';

        return $description;
    }
    // </editor-fold>

    // <editor-fold desc="Functions">
    // =========================================================================
    protected function isConstraintMatches(ResponseInterface $other, Constraint $constraint): bool {
        return (bool) $constraint->evaluate($other, '', true);
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
