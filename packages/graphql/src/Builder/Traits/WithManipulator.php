<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Traits;

use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;

trait WithManipulator {
    protected function getAstManipulator(DocumentAST $document): Manipulator {
        return Container::getInstance()->make(Manipulator::class, [
            'document' => $document,
        ]);
    }
}
