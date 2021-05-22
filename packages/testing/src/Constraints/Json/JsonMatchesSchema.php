<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Json;

use LastDragon_ru\LaraASP\Testing\Constraints\JsonPrettify;
use LastDragon_ru\LaraASP\Testing\Utils\Args;
use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\ValidationResult;
use Opis\JsonSchema\Validator;
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
    /**
     * @inheritdoc
     */
    public function evaluate($other, string $description = '', bool $returnResult = false): ?bool {
        return parent::evaluate(
            Args::getJson($other),
            $description,
            $returnResult,
        );
    }

    /**
     * @inheritdoc
     */
    protected function matches($other): bool {
        $this->result = $this->getValidator()->validate($other, $this->schema->getSchema());
        $matches      = $this->result->isValid();

        return $matches;
    }

    /**
     * @inheritdoc
     */
    protected function failureDescription($other): string {
        return "{$this->prettify($other)} {$this->toString()}";
    }

    /**
     * @inheritdoc
     */
    protected function additionalFailureDescription($other): string {
        $description = parent::additionalFailureDescription($other);

        if ($this->result) {
            $formatted    = (new ErrorFormatter())->format($this->result->error());
            $padding      = '    ';
            $description .= PHP_EOL.$padding.'Errors: ';
            $description .= ltrim(preg_replace('/^/m', $padding, $this->prettify($formatted)));
            $description .= PHP_EOL;
        }

        return $description;
    }

    public function toString(): string {
        return 'matches JSON schema';
    }

    // </editor-fold>

    // <editor-fold desc="Getters">
    // =========================================================================
    protected function getValidator(): Validator {
        $validator = new Validator();
        $validator->resolver()->registerProtocol(Protocol::Scheme, new Protocol());

        return $validator;
    }
    // </editor-fold>
}
