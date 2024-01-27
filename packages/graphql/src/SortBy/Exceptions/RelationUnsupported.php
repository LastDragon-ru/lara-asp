<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Throwable;

use function sprintf;

class RelationUnsupported extends SortByException {
    /**
     * @param class-string<Relation<Model>> $class
     */
    public function __construct(
        protected string $class,
        Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Relation `%s` cannot be used for sort.',
                $this->class,
            ),
            $previous,
        );
    }

    /**
     * @return class-string<Relation<Model>>
     */
    public function getClass(): string {
        return $this->class;
    }
}
