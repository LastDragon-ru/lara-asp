<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links;

use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\PhpClassComment;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Contracts\Link;
use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;

class ClassMethodLink extends Base implements Link {
    public function __construct(
        PhpClassComment $comment,
        string $class,
        public string $method,
    ) {
        parent::__construct($comment, $class);
    }

    #[Override]
    public function __toString(): string {
        return "{$this->class}::{$this->method}()";
    }

    #[Override]
    protected function getTargetNode(ClassLike $class): ?Node {
        return $class->getMethod($this->method);
    }
}
