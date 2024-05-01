<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Utils;

use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\Type;
use LogicException;
use Override;
use UnitEnum;

/**
 * Please note that {@see Type} and {@see NamedType} are used only to be
 * compatible with existing API and will be removed in the next major version
 * (without any BC mark).
 */
class TypeReference extends Type implements NamedType {
    /**
     * @param class-string<(Type&NamedType)|UnitEnum> $type
     */
    public function __construct(
        public readonly string $name,
        public readonly string $type,
    ) {
        // empty
    }

    /**
     * @internal
     */
    #[Override]
    public function name(): string {
        return $this->name;
    }

    /**
     * @internal
     */
    #[Override]
    public function assertValid(): void {
        $this->methodShouldNotBeUsed();
    }

    /**
     * @internal
     */
    #[Override]
    public function isBuiltInType(): bool {
        $this->methodShouldNotBeUsed();
    }

    /**
     * @internal
     */
    #[Override]
    public function description(): ?string {
        $this->methodShouldNotBeUsed();
    }

    /**
     * @internal
     * @inheritDoc
     */
    #[Override]
    public function astNode(): ?Node {
        $this->methodShouldNotBeUsed();
    }

    /**
     * @internal
     * @inheritDoc
     */
    #[Override]
    public function extensionASTNodes(): array {
        $this->methodShouldNotBeUsed();
    }

    /**
     * @internal
     */
    #[Override]
    public function toString(): string {
        $this->methodShouldNotBeUsed();
    }

    private function methodShouldNotBeUsed(): never {
        throw new LogicException(
            'Method exists only for compatibility with existing API and MUST NOT BE USED.',
        );
    }
}
