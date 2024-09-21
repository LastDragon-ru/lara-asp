<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links;

use Closure;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\PhpClassComment;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Contracts\Link;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Contracts\LinkFactory;
use Override;

use function ltrim;
use function preg_match;

use const PREG_UNMATCHED_AS_NULL;

class Factory implements LinkFactory {
    public function __construct(
        protected readonly PhpClassComment $comment,
    ) {
        // empty
    }

    #[Override]
    public function create(string $string, ?Closure $resolver = null): ?Link {
        $reference  = null;
        $identifier = '[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*';
        $regexp     = <<<REGEXP
            /
            ^
            (?P<class>\\\\?(?:{$identifier}\\\\)*{$identifier})
            (?:::(?:
              (?:(?P<method>{$identifier})\\(\\))
              |
              (?:\\$(?P<property>{$identifier}))
              |
              (?P<const>{$identifier})
            ))?
            $
            /imx
            REGEXP;
        $matches    = [];

        if (preg_match($regexp, $string, $matches, PREG_UNMATCHED_AS_NULL)) {
            $class = $matches['class'];
            $class = $resolver ? $resolver($class) : $class;

            if ($class !== null) {
                $class     = '\\'.ltrim($class, '\\');
                $reference = match (true) {
                    isset($matches['property']) => new ClassPropertyLink($this->comment, $class, $matches['property']),
                    isset($matches['method'])   => new ClassMethodLink($this->comment, $class, $matches['method']),
                    isset($matches['const'])    => new ClassConstantLink($this->comment, $class, $matches['const']),
                    default                     => new ClassLink($this->comment, $class),
                };
            }
        }

        return $reference;
    }
}
