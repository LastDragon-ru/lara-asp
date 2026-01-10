<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing;

use Illuminate\Contracts\Cache\Repository as CacheContract;
use Illuminate\Contracts\Config\Repository as ConfigContract;
use Illuminate\Filesystem\Filesystem;
use Nuwave\Lighthouse\Schema\AST\ASTCache;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Override;

use function implode;

/**
 * @internal
 */
class SchemaCache extends ASTCache {
    public function __construct(
        ConfigContract $config,
        Filesystem $filesystem,
        protected CacheContract $cache,
        protected string $key,
    ) {
        parent::__construct($config, $filesystem);
    }

    // <editor-fold desc="Getters / Setters">
    // =========================================================================
    protected function getKey(): string {
        return implode(':', [Package::Name, $this::class, $this->key]);
    }
    // </editor-fold>

    // <editor-fold desc="ASTCache">
    // =========================================================================
    #[Override]
    public function isEnabled(): bool {
        return true;
    }

    #[Override]
    public function set(DocumentAST $documentAST): void {
        $this->cache->set($this->getKey(), $documentAST);
    }

    #[Override]
    public function clear(): void {
        $this->cache->delete($this->getKey());
    }

    #[Override]
    public function fromCacheOrBuild(callable $build): DocumentAST {
        return $this->cache->remember($this->key, null, $build(...));
    }
    //</editor-fold>
}
