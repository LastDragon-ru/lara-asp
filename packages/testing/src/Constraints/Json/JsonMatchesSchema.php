<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Json;

use LastDragon_ru\LaraASP\Testing\Constraints\JsonPrettify;
use LastDragon_ru\LaraASP\Testing\Utils\Args;
use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\ValidationResult;
use Opis\JsonSchema\Validator;
use Override;
use PHPUnit\Framework\Constraint\Constraint;

use function ltrim;
use function preg_replace;

use const PHP_EOL;

/**
 * Check that JSON matches schema.
 *
 * @see https://json-schema.org/
 * @see https://github.com/opis/json-schema
 */
class JsonMatchesSchema extends Constraint {
    use JsonPrettify;

    protected JsonSchema        $schema;
    protected ?ValidationResult $result = null;

    public function __construct(JsonSchema $schema) {
        $this->schema = $schema;
    }

    // <editor-fold desc="\PHPUnit\Framework\Constraint\Constraint">
    // =========================================================================
    #[Override]
    public function evaluate(mixed $other, string $description = '', bool $returnResult = false): ?bool {
        return parent::evaluate(
            Args::getJson($other),
            $description,
            $returnResult,
        );
    }

    #[Override]
    protected function matches(mixed $other): bool {
        $this->result = $this->getValidator()->validate($other, $this->schema->getSchema());
        $matches      = $this->result->isValid();

        return $matches;
    }

    #[Override]
    protected function failureDescription(mixed $other): string {
        return "{$this->prettify($other)} {$this->toString()}";
    }

    #[Override]
    protected function additionalFailureDescription(mixed $other): string {
        $description = parent::additionalFailureDescription($other);

        if ($this->result) {
            $error = $this->result->error();

            if ($error) {
                $formatted    = (new ErrorFormatter())->format($error);
                $padding      = '    ';
                $description .= PHP_EOL.$padding.'Errors: ';
                $description .= ltrim((string) preg_replace('/^/m', $padding, $this->prettify($formatted)));
                $description .= PHP_EOL;
            }
        }

        return $description;
    }

    #[Override]
    public function toString(): string {
        return 'matches JSON schema';
    }

    // </editor-fold>

    // <editor-fold desc="Getters">
    // =========================================================================
    protected function getValidator(): Validator {
        $validator = new Validator();
        $validator->resolver()?->registerProtocol(Protocol::Scheme, new Protocol());

        return $validator;
    }
    // </editor-fold>
}
