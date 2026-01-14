<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Extensions\Requirements;

use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Event;
use PHPUnit\Event\Test\BeforeTestMethodCalled;
use PHPUnit\Event\Test\PreConditionCalled;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Framework\Assert;

use function implode;

use const PHP_EOL;

/**
 * @internal
 */
class Listener {
    public function __construct(
        protected readonly Checker $checker,
    ) {
        // empty
    }

    public function notify(Event $event): void {
        // Supported?
        [$class, $method] = $this->getTarget($event);

        if ($class === null) {
            return;
        }

        // Satisfied?
        $failed    = [];
        $satisfied = $this->checker->isSatisfied($class, $method, $failed);

        if (!$satisfied) {
            Assert::markTestSkipped(implode(PHP_EOL, $failed !== [] ? $failed : ['Unknown requirement.']));
        }
    }

    /**
     * @return array{?class-string, ?string}
     */
    protected function getTarget(Event $event): array {
        $class  = null;
        $method = null;

        if ($event instanceof BeforeTestMethodCalled || $event instanceof PreConditionCalled) {
            // Can be updated after `phpunit/phpunit:11` support drop.
            $class = $event->testClassName();
        } elseif ($event instanceof Prepared) {
            $test = $event->test();

            if ($test instanceof TestMethod) {
                $class  = $test->className();
                $method = $test->methodName();
            }
        } else {
            // empty
        }

        return [$class, $method];
    }
}
