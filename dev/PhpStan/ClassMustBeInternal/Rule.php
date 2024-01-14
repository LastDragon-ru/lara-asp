<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Dev\PhpStan\ClassMustBeInternal;

use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule as RuleContract;
use PHPStan\Rules\RuleErrorBuilder;

use function in_array;
use function pathinfo;
use function sprintf;
use function str_ends_with;
use function str_starts_with;

use const PATHINFO_FILENAME;

/**
 * Makes the `@internal` tag required for all subclasses of the specified class.
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
        /**
         * @var array<array-key, class-string>
         */
        protected readonly array $ignored = [],
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

        if (!($origin instanceof Class_) || $origin->isAnonymous()) {
            return [];
        }

        // Ignored?
        if (in_array((string) $origin->namespacedName, $this->ignored, true)) {
            return [];
        }

        // Internal?
        $reflection = $node->getClassReflection();
        $isInternal = (bool) $reflection->getResolvedPhpDoc()?->isInternal();

        if ($isInternal) {
            return [];
        }

        // Must be internal?
        if ($this->mustBe($reflection)) {
            return [
                RuleErrorBuilder::message(
                    sprintf('Class `%s` must be marked by `@internal`.', $reflection->getName()),
                )->build(),
            ];
        }

        // Return
        return [];
    }

    private function mustBe(ClassReflection $reflection): bool {
        return $this->mustBeIsTestInternal($reflection)
            || $this->mustBeIsInstanceOf($reflection);
    }

    private function mustBeIsInstanceOf(ClassReflection $reflection): bool {
        $mustBe = false;

        foreach ($this->classes as $class) {
            // Instance?
            if ($reflection->is($class) || $reflection->implementsInterface($class)) {
                $mustBe = true;
                break;
            }
        }

        return $mustBe;
    }

    private function mustBeIsTestInternal(ClassReflection $reflection): bool {
        $classname = $reflection->getNativeReflection()->getShortName();
        $filename  = pathinfo((string) $reflection->getFileName(), PATHINFO_FILENAME);
        $mustBe    = $filename
            && str_ends_with($filename, 'Test')
            && str_starts_with($classname, "{$filename}_");

        return $mustBe;
    }
}
