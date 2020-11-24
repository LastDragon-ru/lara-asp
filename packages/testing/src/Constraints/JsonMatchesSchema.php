<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints;

use Opis\JsonSchema\Schema;
use Opis\JsonSchema\ValidationResult;
use Opis\JsonSchema\Validator;
use OpisErrorPresenter\Implementation\MessageFormatterFactory;
use OpisErrorPresenter\Implementation\PresentedValidationErrorFactory;
use OpisErrorPresenter\Implementation\Strategies\BestMatchError;
use OpisErrorPresenter\Implementation\ValidationErrorPresenter;
use PHPUnit\Framework\Constraint\Constraint;
use stdClass;
use const PHP_EOL;

class JsonMatchesSchema extends Constraint {
    use JsonPrettify;

    protected Schema             $schema;
    protected ?ValidationResult  $result = null;

    public function __construct(stdClass $schema) {
        $this->schema = new Schema($schema);
    }

    // <editor-fold desc="\PHPUnit\Framework\Constraint\Constraint">
    // =========================================================================
    protected function matches($other): bool {
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
            $padding     = '  ';
            $description .= PHP_EOL.$padding.'Errors:';
            $description .= PHP_EOL.preg_replace('/^/m', $padding, $this->prettify($presented));
            $description .= PHP_EOL;
        }

        return $description;
    }

    public function toString(): string {
        return 'matches JSON schema';
    }

    // </editor-fold>
}
