<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder;

use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;

/**
 * @see https://github.com/mockery/mockery/issues/1317
 * @internal
 */
class ManipulatorFactory {
    public function __construct(
        private readonly ContainerResolver $containerResolver,
    ) {
        // empty
    }

    public function create(DocumentAST $document): Manipulator {
        return $this->containerResolver->getInstance()->make(Manipulator::class, [
            'document' => $document,
        ]);
    }
}
