<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing;

use Closure;
use Illuminate\Cache\Repository as CacheContract;
use Illuminate\Contracts\Config\Repository as ConfigContract;
use LastDragon_ru\LaraASP\GraphQL\Package;
use Nuwave\Lighthouse\Schema\AST\ASTCache;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;

use function implode;

class SchemaCache extends ASTCache {
    public function __construct(
        ConfigContract $config,
        protected CacheContract $cache,
        protected string $key,
    ) {
        parent::__construct($config);
    }

    // <editor-fold desc="Getters / Setters">
    // =========================================================================
    protected function getKey(): string {
        return implode(':', [Package::Name, $this::class, $this->key]);
    }
    // </editor-fold>

    // <editor-fold desc="ASTCache">
    // =========================================================================
    public function isEnabled(): bool {
        return true;
    }

    public function set(DocumentAST $documentAST): void {
        $this->cache->set($this->getKey(), $documentAST);
    }

    public function clear(): void {
        $this->cache->delete($this->getKey());
    }

    public function fromCacheOrBuild(callable $build): DocumentAST {
        return $this->cache->remember($this->key, null, Closure::fromCallable($build));
    }
    //</editor-fold>
}
