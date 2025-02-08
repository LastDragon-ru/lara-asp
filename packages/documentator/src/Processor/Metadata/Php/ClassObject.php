<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Php;

use PhpParser\NameContext;
use PhpParser\Node\Stmt\ClassLike;

readonly class ClassObject {
    public function __construct(
        public ClassLike $class,
        public NameContext $context,
    ) {
        // empty
    }
}
