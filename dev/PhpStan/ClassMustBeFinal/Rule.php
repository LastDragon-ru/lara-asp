<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Dev\PhpStan\ClassMustBeFinal;

use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule as RuleContract;
use PHPStan\Rules\RuleErrorBuilder;

use function sprintf;

/**
 * Makes the `final` keyword required for all subclasses of the specified class.
 *
 * @internal
 * @implements RuleContract<InClassNode>
 */
class Rule implements RuleContract {
    public function __construct(
        /**
         * @var array<array-key, class-string>
         */
        protected readonly array $classes,
    ) {
        // empty
    }

    #[Override]
    public function getNodeType(): string {
        return InClassNode::class;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function processNode(Node $node, Scope $scope): array {
        // Skip?
        $origin = $node->getOriginalNode();

        if (!($origin instanceof Class_) || $origin->isFinal() || $origin->isAbstract() || $origin->isAnonymous()) {
            return [];
        }

        // Must be final?
        $reflection  = $node->getClassReflection();
        $mustBeFinal = false;

        foreach ($this->classes as $class) {
            if ($reflection->is($class) || $reflection->implementsInterface($class)) {
                $mustBeFinal = true;
                break;
            }
        }

        if ($mustBeFinal) {
            return [
                RuleErrorBuilder::message(
                    sprintf('Class `%s` must be `final`.', $reflection->getName()),
                )->build(),
            ];
        }

        // Nope
        return [];
    }
}
