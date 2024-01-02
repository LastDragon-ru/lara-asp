<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Utils;

use Illuminate\Console\Parser;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use function array_map;
use function implode;
use function is_array;
use function is_bool;
use function is_scalar;

/**
 * Serialize {@see InputArgument}/{@see InputOption} back to signature form.
 *
 * @see Parser
 * @see InputArgument
 * @see InputOption
 * @see https://laravel.com/docs/artisan#defining-input-expectations
 */
class ArtisanSerializer {
    public function getArgumentSignature(InputArgument $argument): string {
        $default   = $argument->getDefault() !== null;
        $signature = $argument->getName();

        if ($default) {
            $signature .= '=';
        }

        if (!$argument->isRequired() && !$default) {
            $signature .= '?';
        }

        if ($argument->isArray()) {
            $signature .= '*';
        }

        if ($default) {
            $signature .= $this->getValue($argument->getDefault());
        }

        return $signature;
    }

    public function getOptionSignature(InputOption $option): string {
        $default   = $option->getDefault() !== null && $option->acceptValue();
        $signature = '--';

        if ($option->getShortcut()) {
            $signature .= $option->getShortcut().'|';
        }

        $signature .= $option->getName();

        if ($default || $option->isValueOptional()) {
            $signature .= '=';
        }

        if ($option->isNegatable()) {
            // todo(documentator): Not yet supported by Laravel :( Check in v11
        }

        if ($option->isArray()) {
            $signature .= '*';
        }

        if ($default) {
            $signature .= $this->getValue($option->getDefault());
        }

        return $signature;
    }

    protected function getValue(mixed $value): string {
        // Signature is a string so all should be fine.
        return match (true) {
            is_array($value)  => implode(',', array_map($this->getValue(...), $value)),
            is_bool($value)   => ($value ? 'true' : 'false'),
            is_scalar($value) => (string) $value,
            default           => '',
        };
    }
}
