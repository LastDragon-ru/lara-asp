<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Exceptions;

use Exception;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Translation\Translator as TranslatorImpl;
use LastDragon_ru\LaraASP\Testing\PackageException;
use Throwable;

use function sprintf;

class TranslatorUnsupported extends Exception implements PackageException {
    /**
     * @param class-string<Translator> $implementation
     */
    public function __construct(
        private string $implementation,
        Throwable $previous = null,
    ) {
        parent::__construct(sprintf(
            'Only `%s` supported, `%s` given.',
            TranslatorImpl::class,
            $this->getImplementation(),
        ), 0, $previous);
    }

    public function getImplementation(): string {
        return $this->implementation;
    }
}
