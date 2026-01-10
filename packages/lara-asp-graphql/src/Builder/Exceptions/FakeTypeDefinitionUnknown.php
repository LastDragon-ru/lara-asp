<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions;

use Throwable;

use function sprintf;

class FakeTypeDefinitionUnknown extends BuilderException {
    public function __construct(
        protected string $name,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Fake type definition `%s` is not exists.',
                $this->name,
            ),
            $previous,
        );
    }

    public function getName(): string {
        return $this->name;
    }
}
