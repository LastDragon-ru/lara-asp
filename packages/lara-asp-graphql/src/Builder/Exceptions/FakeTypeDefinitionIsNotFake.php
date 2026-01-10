<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions;

use Throwable;

use function sprintf;

class FakeTypeDefinitionIsNotFake extends BuilderException {
    public function __construct(
        protected string $name,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Type definition `%s` is not a fake.',
                $this->name,
            ),
            $previous,
        );
    }

    public function getName(): string {
        return $this->name;
    }
}
