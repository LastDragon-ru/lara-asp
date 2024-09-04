<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Reference;

use Closure;
use Stringable;

use function ltrim;
use function preg_match;

use const PREG_UNMATCHED_AS_NULL;

abstract class Reference implements Stringable {
    public function __construct() {
        // empty
    }

    /**
     * @param Closure(string):(string|null)|null $classNameResolver
     */
    public static function parse(string $string, ?Closure $classNameResolver = null): ?self {
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
            $class = $classNameResolver ? $classNameResolver($class) : $class;

            if ($class !== null) {
                $class     = '\\'.ltrim($class, '\\');
                $reference = match (true) {
                    isset($matches['property']) => new ClassPropertyReference($class, $matches['property']),
                    isset($matches['method'])   => new ClassMethodReference($class, $matches['method']),
                    isset($matches['const'])    => new ClassConstantReference($class, $matches['const']),
                    default                     => new ClassReference($class),
                };
            }
        }

        return $reference;
    }
}
