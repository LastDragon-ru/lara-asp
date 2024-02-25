<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions;

use Throwable;

use function sprintf;

class TypeDefinitionInvalidExtension extends BuilderException {
    public function __construct(
        protected string $name,
        protected string $extension,
        Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Type Definition `%s` cannot be extended by `%s` extension.',
                $this->name,
                $this->extension,
            ),
            $previous,
        );
    }

    public function getName(): string {
        return $this->name;
    }

    public function getExtension(): string {
        return $this->extension;
    }
}
