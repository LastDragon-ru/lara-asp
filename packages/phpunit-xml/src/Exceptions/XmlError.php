<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Xml\Exceptions;

use LastDragon_ru\PhpUnit\PackageException;
use LibXMLError;
use Throwable;

use function implode;
use function mb_trim;
use function sprintf;

use const LIBXML_ERR_ERROR;
use const LIBXML_ERR_FATAL;
use const LIBXML_ERR_WARNING;
use const PHP_EOL;

class XmlError extends PackageException {
    public function __construct(
        /**
         * @var non-empty-list<LibXMLError>
         */
        protected readonly array $errors,
        ?Throwable $previous = null,
    ) {
        $errors = [];
        $levels = [
            LIBXML_ERR_WARNING => '(warning)',
            LIBXML_ERR_ERROR   => '(error)',
            LIBXML_ERR_FATAL   => '(fatal)',
        ];

        foreach ($this->errors as $error) {
            $errors[] = sprintf(
                '%s %s (file: `%s`, line: `%s`, code: `%s`)',
                $levels[$error->level],
                mb_trim($error->message),
                $error->file,
                $error->line,
                $error->code,
            );
        }

        parent::__construct(implode(PHP_EOL, $errors), $previous);
    }

    /**
     * @return non-empty-list<LibXMLError>
     */
    public function getErrors(): array {
        return $this->errors;
    }
}
