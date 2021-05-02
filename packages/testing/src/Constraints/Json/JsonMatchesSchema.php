<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Json;

use LastDragon_ru\LaraASP\Testing\Constraints\JsonPrettify;
use LastDragon_ru\LaraASP\Testing\Utils\Args;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\ValidationResult;
use Opis\JsonSchema\Validator;
use OpisErrorPresenter\Implementation\MessageFormatterFactory;
use OpisErrorPresenter\Implementation\PresentedValidationErrorFactory;
use OpisErrorPresenter\Implementation\Strategies\BestMatchError;
use OpisErrorPresenter\Implementation\ValidationErrorPresenter;
use PHPUnit\Framework\Constraint\Constraint;

use function ltrim;
use function preg_replace;

use const PHP_EOL;

/**
 * Check that JSON matches schema (draft-07 and draft-06).
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
        $helper       = null;
        $loader       = $this->schema->getLoader();
        $schema       = new Schema(Args::getJson($this->schema->getSchema()));
        $this->result = (new Validator($helper, $loader))->schemaValidation($other, $schema);
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
            $presenter    = new ValidationErrorPresenter(
                new PresentedValidationErrorFactory(new MessageFormatterFactory()),
                new BestMatchError(),
            );
            $presented    = $presenter->present(...$this->result->getErrors());
            $padding      = '    ';
            $description .= PHP_EOL.$padding.'Errors: ';
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
