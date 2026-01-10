<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links;

use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Contracts\Link;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassLike;

class ClassPropertyLink extends Base implements Link {
    public function __construct(
        string $class,
        public string $property,
    ) {
        parent::__construct($class);
    }

    #[Override]
    public function __toString(): string {
        return "{$this->class}::\${$this->property}";
    }

    #[Override]
    protected function getTargetNode(ClassLike $class): ?Node {
        $node = $class->getProperty($this->property);

        if ($node === null) {
            $constructor = $class->getMethod('__construct');
            $parameters  = $constructor?->getParams() ?? [];

            foreach ($parameters as $parameter) {
                if (!$parameter->isPromoted() || !($parameter->var instanceof Variable)) {
                    continue;
                }

                if ($parameter->var->name === $this->property) {
                    $node = $parameter;
                    break;
                }
            }
        }

        return $node;
    }
}
