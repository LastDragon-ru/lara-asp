<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Traits;

use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;

trait WithManipulator {
    protected function getAstManipulator(DocumentAST $document): AstManipulator {
        return Container::getInstance()->make(AstManipulator::class, [
            'document' => $document,
        ]);
    }

    protected function getManipulator(DocumentAST $document, BuilderInfo $builder): Manipulator {
        return Container::getInstance()->make(Manipulator::class, [
            'document'    => $document,
            'builderInfo' => $builder,
        ]);
    }
}
