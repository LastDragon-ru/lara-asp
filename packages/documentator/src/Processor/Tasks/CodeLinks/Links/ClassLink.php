<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links;

use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Contracts\Link;
use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;

class ClassLink extends Base implements Link {
    #[Override]
    public function __toString(): string {
        return $this->class;
    }

    #[Override]
    protected function getTargetNode(ClassLike $class): ?Node {
        return $class;
    }
}
