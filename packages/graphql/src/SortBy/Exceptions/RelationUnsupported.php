<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions;

use Throwable;

use function implode;
use function sprintf;

class RelationUnsupported extends SortByException {
    /**
     * @param class-string<\Illuminate\Database\Eloquent\Relations\Relation>        $relationClass
     * @param array<class-string<\Illuminate\Database\Eloquent\Relations\Relation>> $supported
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
     * @return class-string<\Illuminate\Database\Eloquent\Relations\Relation>
     */
    public function getRelationClass(): string {
        return $this->relationClass;
    }

    /**
     * @return array<class-string<\Illuminate\Database\Eloquent\Relations\Relation>>
     */
    public function getSupported(): array {
        return $this->supported;
    }
}
