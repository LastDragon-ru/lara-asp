<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Ast;

use Illuminate\Contracts\Container\Container;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use WeakMap;

class Repository {
    /**
     * @var WeakMap<DocumentAST, Metadata>
     */
    protected WeakMap $map;

    public function __construct(
        protected Container $container,
    ) {
        $this->map = new WeakMap();
    }

    public function get(DocumentAST $document): Metadata {
        $metadata = $this->map[$document] ?? null;

        if ($metadata === null) {
            $metadata             = $this->container->make(Metadata::class);
            $this->map[$document] = $metadata;
        }

        return $metadata;
    }
}
