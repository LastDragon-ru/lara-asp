<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Throwable;

use function implode;
use function sprintf;

class RelationUnsupported extends SortByException {
    /**
     * @param class-string<Relation<Model>>       $relationClass
     * @param list<class-string<Relation<Model>>> $supported
     */
    public function __construct(
        protected string $relationName,
        protected string $relationClass,
        protected array $supported,
        Throwable $previous = null,
    ) {
        parent::__construct(sprintf(
            'Relation `%s` of type `%s` cannot be used for sort, only `%s` supported.',
            $this->relationName,
            $this->relationClass,
            implode('`, `', $this->supported),
        ), $previous);
    }

    public function getRelationName(): string {
        return $this->relationName;
    }

    /**
     * @return class-string<Relation<Model>>
     */
    public function getRelationClass(): string {
        return $this->relationClass;
    }

    /**
     * @return list<class-string<Relation<Model>>>
     */
    public function getSupported(): array {
        return $this->supported;
    }
}
