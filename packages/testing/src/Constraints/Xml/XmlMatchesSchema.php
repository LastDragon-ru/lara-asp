<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Xml;

use DOMDocument;
use LastDragon_ru\LaraASP\Testing\Args;
use PHPUnit\Framework\Constraint\Constraint;
use SplFileInfo;
use function libxml_clear_errors;
use function libxml_get_errors;
use function libxml_use_internal_errors;
use function strtolower;
use function trim;
use const LIBXML_ERR_ERROR;
use const LIBXML_ERR_FATAL;
use const LIBXML_ERR_WARNING;
use const PHP_EOL;

abstract class XmlMatchesSchema extends Constraint {
    protected SplFileInfo $schema;
    /**
     * @var \LibXMLError[]
     */
    protected array $errors = [];

    public function __construct(SplFileInfo $schema) {
        $this->schema = $schema;
    }

    // <editor-fold desc="Factory">
    // =========================================================================
    /**
     * @param \SplFileInfo|\DOMDocument|string $xml
     * @param \SplFileInfo                     $schema
     *
     * @return static
     */
    public static function create($xml, SplFileInfo $schema): self {
        $xml        = Args::getFile($xml) ?? Args::getDomDocument($xml) ?? Args::invalidXml();
        $schema     = Args::getFile($schema) ?? Args::invalidFile();
        $isRelaxNg  = strtolower($schema->getExtension()) === 'rng';
        $constraint = null;

        if ($xml instanceof DOMDocument) {
            $constraint = $isRelaxNg
                ? new DomDocumentMatchesSchemaRelaxNg($schema)
                : new DomDocumentMatchesSchemaXsd($schema);
        } else {
            $constraint = $isRelaxNg
                ? new XmlFileMatchesSchemaRelaxNg($schema)
                : new XmlFileMatchesSchemaXsd($schema);
        }

        return $constraint;
    }
    // </editor-fold>

    // <editor-fold desc="Constraint">
    // =========================================================================
    /**
     * @param \SplFileInfo|\DOMDocument|string $other
     *
     * @return bool
     */
    protected function matches($other): bool {
        $previous = libxml_use_internal_errors(true);
        libxml_clear_errors();

        try {
            $matches = $this->isMatchesSchema($other);
        } finally {
            $this->errors = libxml_get_errors();

            libxml_use_internal_errors($previous);
            libxml_clear_errors();
        }

        return $matches;
    }

    /**
     * @param \SplFileInfo|\DOMDocument|string $other
     *
     * @return string
     */
    protected function additionalFailureDescription($other): string {
        $description = parent::additionalFailureDescription($other);
        $levels      = [
            LIBXML_ERR_WARNING => 'Warning',
            LIBXML_ERR_ERROR   => 'Error',
            LIBXML_ERR_FATAL   => 'Fatal Error',
        ];

        foreach ($this->errors as $error) {
            $padding     = '    ';
            $description .= PHP_EOL.$padding.trim("{$levels[$error->level]} #{$error->code}: {$error->message}");
            $description .= PHP_EOL."{$padding}{$error->file}:{$error->line}";
            $description .= PHP_EOL;
        }

        return $description;
    }

    public function toString(): string {
        return "matches schema {$this->schema->getPathname()}";
    }

    // </editor-fold>

    // <editor-fold desc="Abstract">
    // =========================================================================
    /**
     * @param \SplFileInfo|\DOMDocument|string $other
     *
     * @return bool
     */
    protected abstract function isMatchesSchema($other): bool;
    // </editor-fold>
}
