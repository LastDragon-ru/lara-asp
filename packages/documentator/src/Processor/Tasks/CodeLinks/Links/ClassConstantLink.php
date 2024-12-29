<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links;

use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Contracts\Link;
use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\EnumCase;

class ClassConstantLink extends Base implements Link {
    public function __construct(
        string $class,
        public readonly string $constant,
    ) {
        parent::__construct($class);
    }

    #[Override]
    public function __toString(): string {
        return "{$this->class}::{$this->constant}";
    }

    #[Override]
    protected function getTargetNode(ClassLike $class): ?Node {
        // No method :'(
        $target = null;

        if ($class instanceof Enum_) {
            foreach ($class->stmts as $stmt) {
                if ($stmt instanceof EnumCase && (string) $stmt->name === $this->constant) {
                    $target = $stmt;
                    break;
                }
            }
        } else {
            foreach ($class->getConstants() as $constant) {
                foreach ($constant->consts as $const) {
                    if ((string) $const->name === $this->constant) {
                        $target = $constant;
                        break;
                    }
                }
            }
        }

        return $target;
    }
}
