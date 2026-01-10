<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts\Php;

use LastDragon_ru\Path\FilePath;
use PhpParser\NameContext;

readonly class ParsedFile {
    /**
     * @var list<ParsedClass>
     */
    public array $classes;

    /**
     * @param callable(static): list<ParsedClass> $classes
     */
    public function __construct(
        public FilePath $path,
        public NameContext $context,
        callable $classes,
    ) {
        $this->classes = $classes($this);
    }
}
