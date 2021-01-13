<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints;

use LastDragon_ru\LaraASP\Testing\Args;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\ValidationResult;
use Opis\JsonSchema\Validator;
use OpisErrorPresenter\Implementation\MessageFormatterFactory;
use OpisErrorPresenter\Implementation\PresentedValidationErrorFactory;
use OpisErrorPresenter\Implementation\Strategies\BestMatchError;
use OpisErrorPresenter\Implementation\ValidationErrorPresenter;
use PHPUnit\Framework\Constraint\Constraint;
use stdClass;
use function ltrim;
use const PHP_EOL;

/**
 * Check that JSON matches schema (draft-07 and draft-06).
 *
 * @see https://json-schema.org/
 * @see https://github.com/opis/json-schema
 */
class JsonMatchesSchema extends Constraint {
    use JsonPrettify;

    protected Schema            $schema;
    protected ?ValidationResult $result = null;

    /**
     * @param \SplFileInfo|\stdClass|array|string $schema
     */
    public function __construct($schema) {
        $this->schema = new Schema(Args::getJson($schema) ?? Args::invalidJson());
    }

    // <editor-fold desc="\PHPUnit\Framework\Constraint\Constraint">
    // =========================================================================
    /**
     * @param \SplFileInfo|\stdClass|array|string $other
     *
     * @return bool
     */
    protected function matches($other): bool {
        $other        = Args::getJson($other) ?? Args::invalidJson();
        $this->result = (new Validator())->schemaValidation($other, $this->schema);
        $matches      = $this->result->isValid();

        return $matches;
    }

    protected function failureDescription($other): string {
        return $other instanceof stdClass
            ? "{$this->prettify($other)} {$this->toString()}"
            : parent::failureDescription($other);
    }

    protected function additionalFailureDescription($other): string {
        $description = parent::additionalFailureDescription($other);

        if ($this->result) {
            $presenter   = new ValidationErrorPresenter(
                new PresentedValidationErrorFactory(new MessageFormatterFactory()),
                new BestMatchError()
            );
            $presented   = $presenter->present(...$this->result->getErrors());
            $padding     = '    ';
            $description .= PHP_EOL.$padding.'Errors:';
            $description .= ltrim(preg_replace('/^/m', $padding, $this->prettify($presented)));
            $description .= PHP_EOL;
        }

        return $description;
    }

    public function toString(): string {
        return 'matches JSON schema';
    }

    // </editor-fold>
}
