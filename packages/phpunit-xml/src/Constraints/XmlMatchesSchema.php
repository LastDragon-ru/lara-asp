<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Xml\Constraints;

use Closure;
use DOMDocument;
use Exception;
use LastDragon_ru\Path\FilePath;
use LastDragon_ru\PhpUnit\Xml\Exceptions\XmlError;
use Override;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Util\Exporter;
use XMLReader;

use function libxml_clear_errors;
use function libxml_get_errors;
use function libxml_use_internal_errors;
use function mb_trim;

/**
 * Checks that {@see DOMDocument}/XML File matches XSD/RelaxNG schema.
 */
class XmlMatchesSchema extends Constraint {
    protected ?Exception $error = null;

    public function __construct(
        protected readonly FilePath $schema,
    ) {
        // empty
    }

    #[Override]
    public function toString(): string {
        return 'matches schema '.Exporter::export($this->schema->path);
    }

    #[Override]
    protected function failureDescription(mixed $other): string {
        return match (true) {
            $other instanceof FilePath => 'file '.Exporter::export($other->path).' '.$this->toString(),
            default                    => parent::failureDescription($other),
        };
    }

    #[Override]
    protected function additionalFailureDescription(mixed $other): string {
        return mb_trim(parent::additionalFailureDescription($other)."\n".$this->error?->getMessage());
    }

    #[Override]
    public function evaluate(mixed $other, string $description = '', bool $returnResult = false): ?bool {
        $this->error = null;

        try {
            return parent::evaluate($other, $description, $returnResult);
        } finally {
            $this->error = null;
        }
    }

    #[Override]
    protected function matches(mixed $other): bool {
        try {
            return $this->call(fn () => match (true) {
                $other instanceof DOMDocument => $this->dom($other),
                $other instanceof FilePath    => $this->file($other),
                default                       => false,
            });
        } catch (Exception $exception) {
            $this->error = $exception;
        }

        return false;
    }

    private function dom(DOMDocument $document): bool {
        return match ($this->schema->extension) {
            'rng'   => $document->relaxNGValidate($this->schema->path),
            'xsd'   => $document->schemaValidate($this->schema->path),
            default => false,
        };
    }

    private function file(FilePath $file): bool {
        // Create Reader
        $reader = XMLReader::open($file->path);

        if (!($reader instanceof XMLReader)) {
            return false;
        }

        // Schema
        $schema = match ($this->schema->extension) {
            'rng'   => $reader->setRelaxNGSchema($this->schema->path),
            'xsd'   => $reader->setSchema($this->schema->path),
            default => false,
        };

        if (!$schema) {
            return false;
        }

        // Check
        $valid = true;

        try {
            while ($reader->read()) {
                if (!$reader->isValid()) {
                    $valid = false;
                    break;
                }
            }
        } finally {
            $reader->close();
        }

        // Return
        return $valid;
    }

    /**
     * @template T
     *
     * @param Closure(): T $closure
     *
     * @return T
     */
    protected function call(Closure $closure): mixed {
        $errors   = null;
        $result   = null;
        $previous = libxml_use_internal_errors(true);
        libxml_clear_errors();

        try {
            $result = $closure();
        } finally {
            $errors = libxml_get_errors();

            libxml_use_internal_errors($previous);
            libxml_clear_errors();
        }

        if ($errors !== []) {
            throw new XmlError($errors);
        }

        return $result;
    }
}
