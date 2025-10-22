<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts\Php;

use LastDragon_ru\LaraASP\Documentator\Utils\PhpDoc;
use PhpParser\NameContext;
use PhpParser\Node\Stmt\ClassLike;

readonly class ClassComment {
    public function __construct(
        public ClassLike $class,
        public NameContext $context,
        public PhpDoc $comment,
    ) {
        // empty
    }
}
